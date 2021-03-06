<?php

namespace sitkoru\cache\ar;

use yii\db\ActiveRecord;

/**
 * Class ActiveRecordTrait
 *
 * @package sitkoru\cache\ar
 */
trait ActiveRecordTrait
{
    public $fromCache = false;
    public $insert = false;

    public static function find()
    {
        return new CacheActiveQuery(get_called_class());
    }

    public function afterSave($insert, $changedAttributes)
    {
        /**
         * @var $this ActiveRecord
         */
        parent::afterSave($insert, $changedAttributes);
        $this->insert = $insert;
        ActiveQueryCacheHelper::dropCaches($this, $changedAttributes);
    }

    public function afterDelete()
    {
        /**
         * @var $this ActiveRecord
         */
        parent::afterDelete();
        ActiveQueryCacheHelper::dropCaches($this);
    }

    public function refresh()
    {
        /**
         * @var $this ActiveRecord
         */
        ActiveQueryCacheHelper::dropCaches($this);

        return parent::refresh();
    }

    /**
     * @param string $class
     * @param array  $link
     *
     * @return CacheActiveQuery
     */
    public function hasMany($class, $link)
    {
        return $this->applyDropConditions(parent::hasMany($class, $link));
    }

    /**
     * @param string $class
     * @param array  $link
     *
     * @return CacheActiveQuery
     */
    public function hasOne($class, $link)
    {
        return $this->applyDropConditions(parent::hasOne($class, $link));
    }

    /**
     * @param CacheActiveQuery $query
     *
     * @return CacheActiveQuery
     */
    private function applyDropConditions(CacheActiveQuery $query)
    {
        foreach ($query->link as $param => $value) {
            if (isset($this->$value)) {
                $query->dropCacheOnCreate($param, $this->$value);
            }
        }

        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function deleteAll($condition = '', $params = [])
    {
        ActiveQueryCacheHelper::dropCachesForCondition(static::className(), $condition, $params);

        return parent::deleteAll($condition, $params);
    }
}
