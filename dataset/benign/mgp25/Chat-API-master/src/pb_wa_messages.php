<?php

require_once __DIR__.'/func.php';
require_once __DIR__.'/libaxolotl-php/protocol/SenderKeyDistributionMessage.php';
require_once __DIR__.'/libaxolotl-php/ecc/Curve.php';
class SenderKeyGroupMessage extends \ProtobufMessage
{
    const GROUP_ID = 1;
    const SENDER_KEY = 2;
  /* @var array Field descriptors */
  protected static $fields = [
      self::GROUP_ID => [
          'name'     => 'group_id',
          'required' => false,
          'type'     => 7,
      ],
      self::SENDER_KEY => [
          'name'     => 'sender_key',
          'required' => false,
          'type'     => 7,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::GROUP_ID] = null;
      $this->values[self::SENDER_KEY] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getGroupId()
    {
        return $this->values[self::GROUP_ID];
    }

    public function getSenderKey()
    {
        return $this->values[self::SENDER_KEY];
    }

    public function setGroupId($id)
    {
        $this->values[self::GROUP_ID] = $id;
    }

    public function setSenderKey($sender_key)
    {
        $this->values[self::SENDER_KEY] = $sender_key;
    }
}
class SenderKeyGroupData extends \ProtobufMessage
{
    const MESSAGE = 1;
    const SENDER_KEY = 2;
  /* @var array Field descriptors */
  protected static $fields = [
      self::MESSAGE => [
        'name'     => 'message',
        'required' => false,
        'type'     => 7,
      ],
      self::SENDER_KEY => [
          'name'     => 'sender_key',
          'required' => false,
          'type'     => 'SenderKeyGroupMessage',
      ],

  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::MESSAGE] = null;
      $this->values[self::SENDER_KEY] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getMessage()
    {
        return $this->values[self::MESSAGE];
    }

    public function getSenderKey()
    {
        return $this->values[self::SENDER_KEY];
    }

    public function setMessage($data)
    {
        $this->values[self::MESSAGE] = $data;
    }

    public function setSenderKey($sender_key)
    {
        $this->values[self::SENDER_KEY] = $sender_key;
    }
}

/*
  $url = new MediaUrl();
  $url->parseFromString($data);
*/
class MediaUrl extends \ProtobufMessage
{
    const MESSAGE = 1; //full message with the url
    const URL = 2; // only the url
    const UNK_1 = 3;
    const UNK_2 = 4;
    const DESCRIPTION = 5; //Metadata description
    const TITLE = 6; //Page title
    protected static $fields = [
        self::MESSAGE => [
            'name'     => 'message',
            'required' => false,
            'type'     => 7,
        ],
        self::URL => [
            'name'     => 'url',
            'required' => false,
            'type'     => 7,
        ],
        self::UNK_1 => [
            'name'     => 'unknown1',
            'required' => false,
            'type'     => 5,
        ],
        self::UNK_1 => [
            'name'     => 'unknown2',
            'required' => false,
            'type'     => 7,
        ],
        self::DESCRIPTION => [
            'name'     => 'description',
            'required' => false,
            'type'     => 7,
        ],
        self::TITLE => [
            'name'     => 'title',
            'required' => false,
            'type'     => 7,
        ],
    ];

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones.
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::MESSAGE] = null;
        $this->values[self::URL] = null;
        $this->values[self::UNK_1] = null;
        $this->values[self::UNK_2] = null;
        $this->values[self::DESCRIPTION] = null;
        $this->values[self::TITLE] = null;
    }

    /**
     * Returns field descriptors.
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    public function getMessage()
    {
        return $this->values[self::MESSAGE];
    }

    public function getUrl()
    {
        return $this->values[self::URL];
    }

    public function getUnknown1()
    {
        return $this->values[self::UNK_1];
    }

    public function getUnknown2()
    {
        return $this->values[self::UNK_2];
    }

    public function getDescription()
    {
        return $this->values[self::DESCRIPTION];
    }

    public function getTitle()
    {
        return $this->values[self::TITLE];
    }

    public function setMessage($value)
    {
        $this->values[self::MESSAGE] = $value;
    }

    public function setUrl($value)
    {
        $this->values[self::URL] = $value;
    }

    public function setUnknown1($value)
    {
        $this->values[self::UNK_1] = $value;
    }

    public function setUnknown2($value)
    {
        $this->values[self::UNK_2] = $value;
    }

    public function setDescription($value)
    {
        $this->values[self::DESCRIPTION] = $value;
    }

    public function setTitle($value)
    {
        $this->values[self::TITLE] = $value;
    }
}
class ImageMessage extends \ProtobufMessage
{
    const URL = 1;
    const MIMETYPE = 2;
    const CAPTION = 3;
    const SHA256 = 4;
    const LENGTH = 5;
    const HEIGHT = 6;
    const WIDTH = 7;
    const REFKEY = 8;
    const KEY = 9;
    const IV = 10;
    const THUMBNAIL = 11;
  /* @var array Field descriptors */
  protected static $fields = [
      self::URL => [
          'name'     => 'url',
          'required' => false,
          'type'     => 7,
      ],
      self::MIMETYPE => [
          'name'     => 'mimetype',
          'required' => false,
          'type'     => 7,
      ],
      self::CAPTION => [
          'name'     => 'caption',
          'required' => false,
          'type'     => 7,
      ],
      self::SHA256 => [
          'name'     => 'sha256',
          'required' => false,
          'type'     => 7,
      ],
      self::LENGTH => [
          'name'     => 'length',
          'required' => false,
          'type'     => 5,
      ],
      self::HEIGHT => [
          'name'     => 'height',
          'required' => false,
          'type'     => 5,
      ],
      self::WIDTH => [
          'name'     => 'width',
          'required' => false,
          'type'     => 5,
      ],
      self::REFKEY => [
          'name'     => 'refkey',
          'required' => false,
          'type'     => 7,
      ],
      self::KEY => [
          'name'     => 'key',
          'required' => false,
          'type'     => 7,
      ],
      self::IV => [
          'name'     => 'iv',
          'required' => false,
          'type'     => 7,
      ],
      self::THUMBNAIL => [
          'name'     => 'thumbnail',
          'required' => false,
          'type'     => 7,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::URL] = null;
      $this->values[self::MIMETYPE] = null;
      $this->values[self::CAPTION] = null;
      $this->values[self::SHA256] = null;
      $this->values[self::LENGTH] = null;
      $this->values[self::HEIGHT] = null;
      $this->values[self::WIDTH] = null;
      $this->values[self::REFKEY] = null;
      $this->values[self::KEY] = null;
      $this->values[self::IV] = null;
      $this->values[self::THUMBNAIL] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getUrl()
    {
        return $this->values[self::URL];
    }

    public function getMimeType()
    {
        return $this->values[self::MIMETYPE];
    }

    public function getCaption()
    {
        return $this->values[self::CAPTION];
    }

    public function getSha256()
    {
        return $this->values[self::SHA256];
    }

    public function getLength()
    {
        return $this->values[self::LENGTH];
    }

    public function getHeight()
    {
        return $this->values[self::HEIGHT];
    }

    public function getWidth()
    {
        return $this->values[self::WIDTH];
    }

    public function getRefKey()
    {
        return $this->values[self::REFKEY];
    }

    public function getKey()
    {
        return $this->values[self::KEY];
    }

    public function getIv()
    {
        return $this->values[self::IV];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

    public function setUrl($newValue)
    {
        $this->values[self::URL] = $newValue;
    }

    public function setMimeType($newValue)
    {
        $this->values[self::MIMETYPE] = $newValue;
    }

    public function setCaption($newValue)
    {
        $this->values[self::CAPTION] = $newValue;
    }

    public function setSha256($newValue)
    {
        $this->values[self::SHA256] = $newValue;
    }

    public function setLength($newValue)
    {
        $this->values[self::LENGTH] = $newValue;
    }

    public function setHeight($newValue)
    {
        $this->values[self::HEIGHT] = $newValue;
    }

    public function setWidth($newValue)
    {
        $this->values[self::WIDTH] = $newValue;
    }

    public function setRefKey($newValue)
    {
        $this->values[self::REFKEY] = $newValue;
    }

    public function setKey($newValue)
    {
        $this->values[self::KEY] = $newValue;
    }

    public function setIv($newValue)
    {
        $this->values[self::IV] = $newValue;
    }

    public function setThumbnail($newValue)
    {
        $this->values[self::THUMBNAIL] = $newValue;
    }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $thumb = $this->getThumbnail();
        $this->setThumbnail(null);
        $data = parent::serializeToString();
        $data .= hex2bin('8201');
        $data .= $this->WriteUInt32(strlen($thumb));
        $data .= $thumb;
        $this->setThumbnail($thumb);

        return $data;
    }
}

class Location extends \ProtobufMessage
{
    const LATITUDE = 1;
    const LONGITUDE = 2;
    const NAME = 3;
    const DESCRIPTION = 4;
    const URL = 5;
    const THUMBNAIL = 6;
    /* @var array Field descriptors */
    protected static $fields = [
      self::LATITUDE => [
          'name'     => 'Latitude',
          'required' => false,
          'type'     => 1,
      ],
      self::LONGITUDE => [
          'name'     => 'Longitude',
          'required' => false,
          'type'     => 1,
      ],
      self::NAME => [
          'name'     => 'Name',
          'required' => false,
          'type'     => 7,
      ],
      self::DESCRIPTION => [
          'name'     => 'Description',
          'required' => false,
          'type'     => 7,
      ],
      self::URL => [
          'name'     => 'Url',
          'required' => false,
          'type'     => 7,
      ],
      self::THUMBNAIL => [
          'name'     => 'Thumbnail',
          'required' => false,
          'type'     => 7,
      ],

    ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::LATITUDE] = null;
      $this->values[self::LONGITUDE] = null;
      $this->values[self::NAME] = null;
      $this->values[self::DESCRIPTION] = null;
      $this->values[self::URL] = null;
      $this->values[self::THUMBNAIL] = null;
  }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getLatitude()
    {
        return $this->values[self::LATITUDE];
    }

    public function getLongitude()
    {
        return $this->values[self::LONGITUDE];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

    public function getName()
    {
        return $this->values[self::NAME];
    }

    public function getDescription()
    {
        return $this->values[self::DESCRIPTION];
    }

    public function getUrl()
    {
        return $this->values[self::URL];
    }

    public function setName($value)
    {
        $this->values[self::NAME] = $value;
    }

    public function setDescription($value)
    {
        $this->values[self::DESCRIPTION] = $value;
    }

    public function setUrl($value)
    {
        $this->values[self::URL] = $value;
    }

    public function setLatitude($value)
    {
        $this->values[self::LATITUDE] = $value;
    }

    public function setLongitude($value)
    {
        $this->values[self::LONGITUDE] = $value;
    }

    public function setThumbnail($value)
    {
        $this->values[self::THUMBNAIL] = $value;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $thumb = $this->getThumbnail();
        $this->setThumbnail(null);
        $data = parent::serializeToString();
        $data .= hex2bin('8201');
        $data .= $this->WriteUInt32(strlen($thumb));
        $data .= $thumb;
        $this->setThumbnail($thumb);

        return $data;
    }
}

/* May start with 01 thats bad */
class DocumentMessage extends \ProtobufMessage
{
    const URL = 1;
    const MIMETYPE = 2;
    const NAME = 3;
    const SHA256 = 4;
    const LENGTH = 5;
    const UNK_2 = 6;
    const REFKEY = 7;
    const FILENAME = 8;
    const THUMBNAIL = 9;
    /* @var array Field descriptors */
  protected static $fields = [
      self::URL => [
          'name'     => 'url',
          'required' => false,
          'type'     => 7,
      ],
      self::MIMETYPE => [
          'name'     => 'mimetype',
          'required' => false,
          'type'     => 7,
      ],
      self::NAME => [
          'name'     => 'name',
          'required' => false,
          'type'     => 7,
      ],
      self::LENGTH => [
          'name'     => 'length',
          'required' => false,
          'type'     => 5,
      ],
      self::SHA256 => [
          'name'     => 'sha256',
          'required' => false,
          'type'     => 7,
      ],
      self::UNK_2 => [
          'name'     => 'UNK_2',
          'required' => false,
          'type'     => 5,
      ],
      self::REFKEY => [
          'name'     => 'refkey',
          'required' => false,
          'type'     => 7,
      ],
      self::FILENAME => [
          'name'     => 'filename',
          'required' => false,
          'type'     => 7,
      ],
      self::THUMBNAIL => [
          'name'     => 'thumbnail',
          'required' => false,
          'type'     => 7,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::URL] = null;
      $this->values[self::MIMETYPE] = null;
      $this->values[self::NAME] = null;
      $this->values[self::LENGTH] = null;
      $this->values[self::SHA256] = null;
      $this->values[self::UNK_2] = null;
      $this->values[self::REFKEY] = null;
      $this->values[self::FILENAME] = null;
      $this->values[self::THUMBNAIL] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getUrl()
    {
        return $this->values[self::URL];
    }

    public function getMimeType()
    {
        return $this->values[self::MIMETYPE];
    }

    public function getLength()
    {
        return $this->values[self::LENGTH];
    }

    public function getName()
    {
        return $this->values[self::NAME];
    }

    public function getUNK2()
    {
        return $this->values[self::UNK2];
    }

    public function getRefKey()
    {
        return $this->values[self::REFKEY];
    }

    public function getFilename()
    {
        return $this->values[self::FILENAME];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

    public function setUrl($newValue)
    {
        $this->values[self::URL] = $newValue;
    }

    public function setMimeType($newValue)
    {
        $this->values[self::MIMETYPE] = $newValue;
    }

    public function setName($newValue)
    {
        $this->values[self::NAME] = $newValue;
    }

    public function setSha256($newValue)
    {
        $this->values[self::SHA256] = $newValue;
    }

    public function setLength($newValue)
    {
        $this->values[self::LENGTH] = $newValue;
    }

    public function setRefKey($newValue)
    {
        $this->values[self::REFKEY] = $newValue;
    }

    public function setThumbnail($newValue)
    {
        $this->values[self::THUMBNAIL] = $newValue;
    }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $thumb = $this->getThumbnail();
        $this->setThumbnail(null);
        $data = parent::serializeToString();
        $data .= hex2bin('8201');
        $data .= $this->WriteUInt32(strlen($thumb));
        $data .= $thumb;
        $this->setThumbnail($thumb);

        return $data;
    }
}
