/**
 * Animate watch/unwatch links to use asynchronous API requests to
 * watch pages, rather than navigating to a different URI.
 *
 * Usage:
 *
 *     var watch = require( 'mediawiki.page.watch.ajax' );
 *     watch.updateWatchLink(
 *         $node,
 *         'watch',
 *         'loading'
 *     );
 *
 * @class mw.plugin.page.watch.ajax
 * @singleton
 */
( function () {
	// The name of the page to watch or unwatch
	var pageTitle = mw.config.get( 'wgRelevantPageName' );

	/**
	 * Update the link text, link href attribute and (if applicable)
	 * "loading" class.
	 *
	 * @param {jQuery} $link Anchor tag of (un)watch link
	 * @param {string} action One of 'watch', 'unwatch'
	 * @param {string} [state="idle"] 'idle' or 'loading'. Default is 'idle'
	 */
	function updateWatchLink( $link, action, state ) {
		var msgKey, $li, otherAction;

		// A valid but empty jQuery object shouldn't throw a TypeError
		if ( !$link.length ) {
			return;
		}

		// Invalid actions shouldn't silently turn the page in an unrecoverable state
		if ( action !== 'watch' && action !== 'unwatch' ) {
			throw new Error( 'Invalid action' );
		}

		msgKey = state === 'loading' ? action + 'ing' : action;
		otherAction = action === 'watch' ? 'unwatch' : 'watch';
		$li = $link.closest( 'li' );

		// Trigger a 'watchpage' event for this List item.
		// Announce the otherAction value as the first param.
		// Used to monitor the state of watch link.
		// TODO: Revise when system wide hooks are implemented
		if ( state === undefined ) {
			$li.trigger( 'watchpage.mw', otherAction );
		}

		$link
			// The following messages can be used here:
			// * watch
			// * watching
			// * unwatch
			// * unwatching
			.text( mw.msg( msgKey ) )
			.attr( 'title', mw.msg( 'tooltip-ca-' + action ) )
			.updateTooltipAccessKeys()
			.attr( 'href', mw.util.getUrl( pageTitle, { action: action } ) );

		// Most common ID style
		if ( $li.prop( 'id' ) === 'ca-' + otherAction ) {
			$li.prop( 'id', 'ca-' + action );
		}

		if ( state === 'loading' ) {
			$link.addClass( 'loading' );
		} else {
			$link.removeClass( 'loading' );

			// Remove the half-star class that might have been added by SkinTemplate.
			if ( action === 'unwatch' ) {
				$li.removeClass( 'mw-watchlink-temp' );
			}
		}
	}

	/**
	 * TODO: This should be moved somewhere more accessible.
	 *
	 * @private
	 * @param {string} url
	 * @return {string} The extracted action, defaults to 'view'
	 */
	function mwUriGetAction( url ) {
		var action, actionPaths, key, m, parts;

		// TODO: Does MediaWiki give action path or query param
		// precedence? If the former, move this to the bottom
		action = mw.util.getParamValue( 'action', url );
		if ( action !== null ) {
			return action;
		}

		actionPaths = mw.config.get( 'wgActionPaths' );
		for ( key in actionPaths ) {
			parts = actionPaths[ key ].split( '$1' );
			parts = parts.map( mw.util.escapeRegExp );
			m = new RegExp( parts.join( '(.+)' ) ).exec( url );
			if ( m && m[ 1 ] ) {
				return key;
			}
		}

		return 'view';
	}

	/**
	 * @private
	 */
	function init() {
		var $links = $( '.mw-watchlink a[data-mw="interface"], a.mw-watchlink[data-mw="interface"]' );
		if ( !$links.length ) {
			// Fallback to the class-based exclusion method for backwards-compatibility
			$links = $( '.mw-watchlink a, a.mw-watchlink' );
			// Restrict to core interfaces, ignore user-generated content
			$links = $links.filter( ':not( #bodyContent *, #content * )' );
		}
		if ( $links.length ) {
			// eslint-disable-next-line no-use-before-define
			watchstar( $links, pageTitle, function ( $link, isWatched ) {
				// Update the "Watch this page" checkbox on action=edit when the
				// page is watched or unwatched via the tab (T14395).
				$( '#wpWatchthis' ).prop( 'checked', isWatched === true );
			} );
		}
	}

	/**
	 * Bind a given watchstar element to make it interactive.
	 *
	 * NOTE: This is meant to allow binding of watchstars for arbitrary page titles,
	 * especially if different from the currently viewed page. As such, this function
	 * will *not* synchronise its state with any "Watch this page" checkbox such as
	 * found on the "Edit page" and "Publish changes" forms. The caller should either make
	 * "current page" watchstars picked up by #init (and not use #watchstar) sync it manually
	 * from the callback #watchstar provides.
	 *
	 * @param {jQuery} $links One or more anchor elements that must have an href
	 *  with a url containing a `action=watch` or `action=unwatch` query parameter,
	 *  from which the current state will be learned (e.g. link to unwatch is currently watched)
	 * @param {string} title Title of page that this watchstar will affect
	 * @param {Function} callback Callback to run after the action has been processed and API
	 *  request completed. The callback receives two parameters:
	 * @param {jQuery} callback.$link The element being manipulated
	 * @param {boolean} callback.isWatched Whether the article is now watched
	 */
	function watchstar( $links, title, callback ) {
		$links.on( 'click', function ( e ) {
			var mwTitle, action, api, $link;

			mwTitle = mw.Title.newFromText( title );
			action = mwUriGetAction( this.href );

			if ( !mwTitle || ( action !== 'watch' && action !== 'unwatch' ) ) {
				// Let native browsing handle the link
				return true;
			}
			e.preventDefault();
			e.stopPropagation();

			$link = $( this );

			// eslint-disable-next-line no-jquery/no-class-state
			if ( $link.hasClass( 'loading' ) ) {
				return;
			}

			updateWatchLink( $link, action, 'loading' );

			// Preload the notification module for mw.notify
			mw.loader.load( 'mediawiki.notification' );

			api = new mw.Api();

			api[ action ]( title )
				.done( function ( watchResponse ) {
					var message,
						watchlistPopup = null,
						isWatchlistExpiryEnabled = require( './config.json' ).WatchlistExpiry,
						otherAction = action === 'watch' ? 'unwatch' : 'watch';

					if ( mwTitle.isTalkPage() ) {
						message = action === 'watch' ? 'addedwatchtext-talk' : 'removedwatchtext-talk';
					} else {
						message = action === 'watch' ? 'addedwatchtext' : 'removedwatchtext';
					}

					// @since 1.35 - pop up notification will be loaded with OOUI
					// only if Watchlist Expiry is enabled
					if ( isWatchlistExpiryEnabled ) {
						mw.loader.using( 'mediawiki.watchstar.widgets' ).done( function ( require ) {
							var WatchlistExpiryWidget = require( 'mediawiki.watchstar.widgets' );

							if ( !watchlistPopup ) {
								watchlistPopup = new WatchlistExpiryWidget( {
									// The following messages can be used here:
									// * addedwatchtext-talk
									// * addedwatchtext
									// * removedwatchtext-talk
									// * removedwatchtext
									message: mw.message( message, mwTitle.getPrefixedText() ).parseDom()
								} );
							}

							mw.notify( watchlistPopup.$element, {
								tag: 'watch-self',
								autoHide: true
							} );

						} );
					} else {
						// The following messages can be used here:
						// * addedwatchtext-talk
						// * addedwatchtext
						// * removedwatchtext-talk
						// * removedwatchtext
						mw.notify( mw.message( message, mwTitle.getPrefixedText() ).parseDom(), {
							tag: 'watch-self'
						} );
					}

					// Set link to opposite
					updateWatchLink( $link, otherAction );
					callback( $link, watchResponse.watched === true );
				} )
				.fail( function ( code, data ) {
					var $msg;

					// Reset link to non-loading mode
					updateWatchLink( $link, action );

					// Format error message
					$msg = api.getErrorMessage( data );

					// Report to user about the error
					mw.notify( $msg, {
						tag: 'watch-self',
						type: 'error'
					} );
				} );
		} );
	}

	$( init );

	// Expose public methods.
	module.exports = {
		watchstar: watchstar,
		updateWatchLink: updateWatchLink
	};

}() );
