<?php
/**
 * SerializeBehavior class file.
 * @copyright (c) 2015, Pavel Bariev
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\yii2Tools\behaviors;
use bariew\yii2Tools\helpers\HtmlHelper;
use yii\base\Behavior;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * ActiveRecord behavior for serializing/json encoding
 * array data before saving it to db
 *
 * Usage: set in model
    public function behaviors()
    {
        return [
            ['class' => 'bariew\yii2Tools\behaviors\SerializeBehavior', 'attributes' => ['myAttribute']]
       ];
    }
 *
 * @property ActiveRecord $owner
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class SerializeBehavior extends Behavior
{
    const TYPE_JSON = 'json';
    const TYPE_PHP = 'php';

    public $attributes = [];
    public $type = self::TYPE_JSON;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT => 'unserializeAttributes',
            ActiveRecord::EVENT_AFTER_FIND => 'unserializeAttributes',
            ActiveRecord::EVENT_BEFORE_INSERT => 'serializeAttributes',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'serializeAttributes'
        ];
    }

    /**
     * Serializes data
     * @throws Exception
     */
    public function serializeAttributes()
    {
        foreach ($this->attributes as $key => $attribute) {
            $attribute = is_numeric($key) ? $attribute : $key;
            $value = $this->owner->getAttribute($attribute);
            switch ($this->type) {
                case static::TYPE_JSON :
                    $value = json_encode($value);
                    break;
                case static::TYPE_PHP :
                    $value = serialize($value);
                    break;
                default: throw new Exception("Unknown type: ". $this->type);
            }
            $this->owner->setAttribute($attribute, $value);
        }
    }

    /**
     * Unserializes data
     * @throws Exception
     */
    public function unserializeAttributes()
    {
        foreach ($this->attributes as $key => $attribute) {
            $default = is_numeric($key) ? [] : $attribute;
            $attribute = is_numeric($key) ? $attribute : $key;
            $value = $this->owner->getAttribute($attribute);
            if (is_array($value)) {
                continue;
            }
            switch ($this->type) {
                case static::TYPE_JSON :
                    $value = $value ? json_decode($value, true) : [];
                    break;
                case static::TYPE_PHP :
                    $value = $value ? unserialize($value) : [];
                    break;
                default: throw new Exception("Unknown type: ". $this->type);
            }
            $value = (!$value && $this->owner->isNewRecord) ? $default : $value;
            $this->owner->setAttribute($attribute, $value);
        }
    }

    public function labeledAttribute($attribute)
    {
        $result = [];
        foreach ($this->owner->$attribute as $key => $value) {
            $label = $this->owner->getAttributeLabel($key) ? : $key;
            $result[$label] = $value;
        }
        return $result;

    }

    public function prettyPrint($attribute)
    {
        return HtmlHelper::arrayPrettyPrint($this->labeledAttribute($attribute));
    }
}
