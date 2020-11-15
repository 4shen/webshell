<?php

namespace Telegram\Bot\Objects\InlineQuery;

/**
 * Class InlineQueryResultAudio.
 *
 * <code>
 * $params = [
 *   'id'                         => '',
 *   'audio_url'                  => '',
 *   'title'                      => '',
 *   'caption'                    => '',
 *   'parse_mode'                 => '',
 *   'performer'                  => '',
 *   'audio_duration'             => '',
 *   'reply_markup'               => '',
 *   'input_message_content'      => '',
 * ];
 * </code>
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultaudio
 *
 * @method $this setId($string)                     Unique identifier for this result, 1-64 bytes
 * @method $this setAudioUrl($string)               A valid URL for the audio file
 * @method $this setTitle($string)                  Title
 * @method $this setCaption($string)                Optional. Caption, 0-200 characters
 * @method $this setParseMode($string)              Optional. Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in the media caption.
 * @method $this setPerformer($string)              Optional. Performer
 * @method $this setAudioDuration($int)             Optional. Audio duration in seconds
 * @method $this setReplyMarkup($object)            Optional. Inline keyboard attached to the message
 * @method $this setInputMessageContent($object)    Optional. Content of the message to be sent instead of the photo
 */
class InlineQueryResultAudio extends InlineBaseObject
{
    protected $type = 'audio';
}
