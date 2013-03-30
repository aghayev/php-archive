<?php

/**
 * Listing Hitrate Report Model.
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 *
 */
class ListingChannelHitratePeer extends BaseListingChannelHitratePeer
{

    /**
     * Increase counters
     * 
     * @return void
     */
    public static function increase($listingDetails,
                                    $channel = ChannelPeer::CHANNEL_WEB)
    {
        $ids = array();

        if (count($listingDetails)) {
            // Collects Listing Ids
            foreach ($listingDetails as $listingDetail) {
                $ids[] = $listingDetail->getListing()->getId();
            }

            // Set Channel
            if ($channel == ChannelPeer::CHANNEL_WEB) {
                $channelName1 = 'web';
                $channelName2 = 'wap';
                $channelName3 = 'ussd';
            } elseif ($channel == ChannelPeer::CHANNEL_WAP) {
                $channelName1 = 'wap';
                $channelName2 = 'web';
                $channelName3 = 'ussd';
            } elseif ($channel == ChannelPeer::CHANNEL_USSD) {
                $channelName1 = 'ussd';
                $channelName2 = 'web';
                $channelName3 = 'wap';
            }

            $connection = Propel::getConnection();

            $query = 'UPDATE listing_channel_hitrate 
              SET ' . $channelName1 . '_hitrate=' . $channelName1 . '_hitrate+1, average_hitrate =
                ROUND(' . $channelName3 . '_hitrate+' . $channelName2 . '_hitrate+' . $channelName1 . '_hitrate+1/
              (CASE WHEN DATE_PART(\'day\',now()-live_at) = 0 THEN 1 ELSE DATE_PART(\'day\',now()-live_at) END)),
               updated_at = \'' . date('Y-m-d H:i:s',
                                                                                                                                                                                                                                                                                                                                                                                                                                     stdTime::utcTime()) . '\'
               WHERE listing_id in (' . implode(',',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               $ids) . ') AND live_at IS NOT NULL';

            $statement = $connection->prepare($query);
            $statement->execute();
        }
    }

    /**
     * Create default Listing Channel Hitrate
     *
     * @return Listing
     */
    public static function createDefault(Listing $listing)
    {
        $listingChannelHitrate = self::getListingChannelHitrate($listing->getId());

        if ($listingChannelHitrate) {
            $listingChannelHitrate->setLiveAt(stdTime::utcTime(), 'UTC');
        } else {
            $listingChannelHitrate = new ListingChannelHitrate();
            $listingChannelHitrate->setListingId($listing->getId());
            $listingChannelHitrate->setCreatedAt(stdTime::utcTime(), 'UTC');
        }

        $listingChannelHitrate->save();

        return $listingChannelHitrate;
    }

    /**
     * Get listingChannelHitrate by ListingId 
     * 
     * @return Listings
     */
    public static function getListingChannelHitrate($listingId)
    {
        $c = new Criteria();
        $c->add(self::LISTING_ID, $listingId);

        return self::doSelectOne($c);
    }

    /**
     * Select Listing Usage Records
     *
     * @return array
     */
    public static function selectListingUsageRecords(Criteria $c2)
    {
        $c = new Criteria();
        $c->addJoin(self::LISTING_ID, ListingPeer::ID);
        $c->addJoin(ListingPeer::ID, ListingDetailPeer::LISTING_ID);

        $c->addJoin(ListingDetailPeer::ID, ListingDetailI18nPeer::ID);

        $culture = sfPropel::getDefaultCulture();
        $c->add(ListingDetailI18nPeer::CULTURE, $culture);

        $c->addJoin(ListingDetailPeer::ID,
                    ListingDetailCategoryPeer::LISTING_DETAIL_ID);

        $c->addJoin(ListingDetailPeer::USR_ID, UsrPeer::ID);

        $c->add(ListingDetailPeer::LISTING_DETAIL_STATE_ID,
                ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE);

        $c->mergeWith($c2);

        if (!$c->containsKey(ListingChannelHitratePeer::AVERAGE_HITRATE)) {
            $c->add(ListingChannelHitratePeer::AVERAGE_HITRATE, 0,
                    Criteria::GREATER_THAN);
        }

        $c->addDescendingOrderByColumn(self::AVERAGE_HITRATE);

        return self::doSelect($c);
    }

    /**
     * Count Listing Usage Records
     *
     * @return array
     */
    public static function countListingUsageRecords(Criteria $c2,
                                                    $distinct = false,
                                                    PropelPDO $con = null)
    {
        $c = new Criteria();
        $c->addJoin(self::LISTING_ID, ListingPeer::ID);
        $c->addJoin(ListingPeer::ID, ListingDetailPeer::LISTING_ID);
        $c->addJoin(ListingDetailPeer::ID, ListingDetailI18nPeer::ID);

        $culture = sfPropel::getDefaultCulture();
        $c->add(ListingDetailI18nPeer::CULTURE, $culture);

        $c->addJoin(ListingDetailPeer::ID,
                    ListingDetailCategoryPeer::LISTING_DETAIL_ID);

        $c->addJoin(ListingDetailPeer::USR_ID, UsrPeer::ID);

        $c->add(ListingDetailPeer::LISTING_DETAIL_STATE_ID,
                ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE);

        $c->mergeWith($c2);

        if (!$c->containsKey(ListingChannelHitratePeer::AVERAGE_HITRATE)) {
            $c->add(ListingChannelHitratePeer::AVERAGE_HITRATE, 0,
                    Criteria::GREATER_THAN);
        }

        return self::doCount($c, $distinct, $con);
    }

    /**
     * Create listing categories using COPY command
     *
     * @return categories
     */
    public static function createListingChannelHitrates(&$csvArray,
                                                        $minListingID)
    {
        try {

            $connection = Propel::getConnection();
            $result = array('minID' => 0, 'maxID' => 0, 'newRecs' => 0, 'error' => '');
            $result['newRecs'] = $listingCount = count($csvArray);
            $now = date('Y-m-d H:i:s');
            $maxListingID = $minListingID + $listingCount;

            // If no work to do then return
            if ($listingCount == 0) {

                return $result;
            }

            $columns = array(
                'listing_id',
                'created_at',
            );
            $data = array();
            $lineCount = 1;
            for ($l = $minListingID; $l < $maxListingID; $l++) {
                $data[] = array(
                    $l,
                    $now,
                );
                $lineCount++;
            }
            $success = stdDb::copyIntoTable('listing_channel_hitrate', $columns,
                                            $data);
            if ($success !== true) {
                $result['error'] = $success;
            }
        } catch (Exception $e) {
            stdLog::log(__METHOD__ . ' ### ### Copy ID: ' . $l . ' Error: ' . $e->getMessage(),
                        null, sfLogger::ERR);
            $result['error'] = $e->getCode() . ' ' . $e->getMessage();
        }

        return $result;
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
            if ($name == 'created__by') {
                $column = UsrPeer::USERNAME;
            }
        }

        return isset($column) ? $column : parent::translateFieldName($name,
                                                                     $fromType,
                                                                     $toType);
    }
}

// ListingChannelHitratePeer
