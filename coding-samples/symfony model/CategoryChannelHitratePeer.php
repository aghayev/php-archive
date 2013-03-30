<?php

/**
 * Category Hitrate Report Model.
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 *
 */
class CategoryChannelHitratePeer extends BaseCategoryChannelHitratePeer
{

    /**
     * Creates a new Category Usage Records
     *
     * @return void
     * 
     */
    public static function createCategoryUsageRecords()
    {
        $connection = Propel::getConnection();

        $query = 'INSERT INTO ' . self::TABLE_NAME
            . '(' . implode(',', self::$fieldNames[BasePeer::TYPE_FIELDNAME]) . ') 
            SELECT ' . SearchStatisticsPeer::SEARCH_CATEGORY_ID . ', 
            DATE(' . SearchStatisticsPeer::SEARCHED_AT . ') as search_date, 
            MIN(' . SearchStatisticsPeer::ID . '), 
            MAX(' . SearchStatisticsPeer::ID . '), 
            COUNT(web.ID) as web_hitrate, 
            COUNT(wap.ID) as wap_hitrate, 
            COUNT(ussd.ID) as ussd_hitrate 
            FROM ' . SearchStatisticsPeer::TABLE_NAME . ' 
            LEFT JOIN ' . SearchStatisticsPeer::TABLE_NAME . ' web ON 
            (' . SearchStatisticsPeer::ID . '=web.ID AND web.channel_id=' . ChannelPeer::CHANNEL_WEB . ') 
            LEFT JOIN ' . SearchStatisticsPeer::TABLE_NAME . ' wap ON 
            (' . SearchStatisticsPeer::ID . '=wap.ID AND wap.channel_id=' . ChannelPeer::CHANNEL_WAP . ') 
            LEFT JOIN ' . SearchStatisticsPeer::TABLE_NAME . ' ussd ON 
            (' . SearchStatisticsPeer::ID . '=ussd.ID AND ussd.channel_id=' . ChannelPeer::CHANNEL_USSD . ') 
            WHERE ' . SearchStatisticsPeer::SEARCH_CATEGORY_ID . ' IS NOT NULL 
            AND ' . SearchStatisticsPeer::ID . '> (SELECT COALESCE(MAX(' . self::MAX_SEARCH_ID . '), 0) 
            FROM ' . self::TABLE_NAME . ') 
            GROUP BY search_date,' . SearchStatisticsPeer::SEARCH_CATEGORY_ID;

        $statement = $connection->prepare($query);

        $statement->execute();
        $rowCount = $statement->rowCount();

        return $rowCount;
    }

    /**
     * DoSelect Category Usage Records
     *
     * @return array
     */
    public static function selectCategoryUsageRecords(Criteria $c,
                                                      $culture = null,
                                                      $con = null,
                                                      $joinBehavior = Criteria::LEFT_JOIN)
    {
        $c->addSelectColumn(self::CATEGORY_ID);
        $c->addSelectColumn(self::DATE);

        // temp need to find how to limit Symfony Admin Generator
        // not to select all columns from a table
        $c->addSelectColumn('SUM(' . self::MIN_SEARCH_ID . ') as min_search_id');
        $c->addSelectColumn('SUM(' . self::MAX_SEARCH_ID . ') as max_search_id');

        $c->addSelectColumn('SUM(' . self::WEB_HITRATE . ') as web_hitrate');
        $c->addSelectColumn('SUM(' . self::WAP_HITRATE . ') as wap_hitrate');
        $c->addSelectColumn('SUM(' . self::USSD_HITRATE . ') as ussd_hitrate');

        $c->addJoin(self::CATEGORY_ID, CategoryI18nPeer::ID,
                    Criteria::INNER_JOIN);

        if ($culture == null) {
            $culture = sfPropel::getDefaultCulture();
        }

        $c->add(CategoryI18nPeer::CULTURE, $culture);

        $c->addGroupByColumn(self::CATEGORY_ID);
        $c->addGroupByColumn(CategoryI18nPeer::NAME);
        $c->addGroupByColumn(self::DATE);
        $c->addDescendingOrderByColumn(self::DATE);

        return self::doSelect($c);
    }

    /**
     * DoCount Category Usage Records
     *
     * @return array
     */
    public static function countCategoryUsageRecords(Criteria $c,
                                                     $distinct = false,
                                                     PropelPDO $con = null)
    {
        $c->addSelectColumn(self::CATEGORY_ID);
        $c->addSelectColumn(self::DATE);

        $c->addJoin(self::CATEGORY_ID, CategoryI18nPeer::ID,
                    Criteria::INNER_JOIN);

        $c->add(CategoryI18nPeer::CULTURE, sfPropel::getDefaultCulture());

        $c->addGroupByColumn(self::CATEGORY_ID);
        $c->addGroupByColumn(CategoryI18nPeer::NAME);
        $c->addGroupByColumn(self::DATE);

        return self::doCount($c, $distinct, $con);
    }

    /**
     * Used by generator to sort custom fields
     *
     * @param      string $name field name
     * @param      string $fromType
     * @param      string $toType
     * @return     string translated name of the field.
     */
    static public function translateFieldName($name, $fromType, $toType)
    {
        // Cache generator will fail without this IF
        if ($fromType == 'fieldName' && $toType == 'colName') {
            if ($name == 'category__name') {
                $column = CategoryI18nPeer::NAME;
            }
        }

        return isset($column) ? $column : parent::translateFieldName($name,
                                                                     $fromType,
                                                                     $toType);
    }

}