<?php

namespace Telegram\Bot\Objects\InlineQuery;

/**
 * Class InlineQueryResultCachedAudio.
 *
 * <code>
 * $params = [
 *   'id'                         => '',
 *   'audio_file_id'              => '',
 *   'caption'                    => '',
 *   'parse_mode'                 => '',
 *   'reply_markup'               => '',
 *   'input_message_content'      => '',
 * ];
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultcachedaudio
 *
 * @method $this setId($string)                     Unique identifier for this result, 1-64 bytes
 * @method $this setAudioFileId($string)            A valid file identifier for the audio file
 * @method $this setCaption($string)                Optional. Caption, 0-200 characters
 * @method $this setParseMode($string)              Optional. Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in the media caption.
 * @method $this setReplyMarkup($object)            Optional. Inline keyboard attached to the message
 * @method $this setInputMessageContent($object)    Optional. Content of the message to be sent instead of the photo
 */
class InlineQueryResultCachedAudio extends InlineBaseObject
{
    protected $type = 'audio';
}
