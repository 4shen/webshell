<?php

namespace Elgg\Likes;

use Elgg\Services\AjaxResponse;

/**
 * Ajax response handler
 */
class AjaxResponseHandler {

	/**
	 * Alter ajax response to send back likes count
	 *
	 * @param \Elgg\Hook $hook AjaxResponse::RESPONSE_HOOK, 'all'
	 *
	 * @return void|\Elgg\Services\AjaxResponse
	 */
	public function __invoke(\Elgg\Hook $hook) {
		$entity = get_entity(get_input('guid'));
		if (!$entity) {
			return;
		}
		
		$response = $hook->getValue();
		
		$response->getData()->likes_status = [
			'guid' => $entity->guid,
			'count' => likes_count($entity),
			'count_menu_item' => elgg_view_menu_item(_likes_count_menu_item($entity)),
			'like_menu_item' => elgg_view_menu_item(_likes_menu_item($entity)),
			'is_liked' => DataService::instance()->currentUserLikesEntity($entity->guid),
		];
		
		return $response;
	}
}
