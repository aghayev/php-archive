<?php

/**
 * Manipulate Listing Details
 * 
 * @subpackage model
 * @author     Imran Aghayev
 * @version    $Id$
 */
class ListingDetailPeer extends BaseListingDetailPeer
{
    const ID_ERROR_NUMBER_OF_ROWS_INVALID = 3;
    const ID_ERROR_HEADING_NOT_FOUND = 4;
    const ID_ERROR_DATA_NOT_FOUND = 5;
    const ID_ERROR_SAVE_UPLOADED_FILE = 6;
    const ID_ERROR_SAVE_LISTING_BATCH_DB = 7;
    const ID_ERROR_APPROVE_LISTINGS = 8;
    const ID_ERROR_DELETE_LISTINGS = 9;
    const ID_ERROR_NUMBER_OF_FIELDS_INVALID = 10;

    const ID_ERROR_CATEGORY_EMPTY = 11;
    const ID_ERROR_CATEGORY_INVALID = 12;

    const ID_ERROR_PACKAGE_EMPTY = 13;
    const ID_ERROR_PACKAGE_INVALID = 14;

    // const ID_ERROR_TELEPHONE_EMPTY = 15;
    const ID_ERROR_TELEPHONE_INVALID = 16;

    const ID_ERROR_LONLAT_EMPTY = 17;
    const ID_ERROR_LONLAT_INVALID = 18;

    const ID_ERROR_NAME_EMPTY = 19;
    const ID_ERROR_NAME_INVALID = 20;

    const ID_ERROR_ADDRESS_EMPTY = 21;
    const ID_ERROR_ADDRESS_INVALID = 22;

    const ID_ERROR_CITY_EMPTY = 23;
    const ID_ERROR_CITY_INVALID = 24;

    const ID_ERROR_FAX_INVALID = 25;
    const ID_ERROR_WEB_INVALID = 26;

    private static $actions = array('CREATE', 'UPDATE', 'APPROVE', 'EXTEND', 'DELETE');

    /**
     * Create default ListingDetail
     *
     * @param Listing $listing
     * @return ListingDetail
     */
    public static function createDefault(Listing $listing = null)
    {
        if (!$listing) {
            // Create listing
            $listing = ListingPeer::createDefault();
        }

        // Create listing detail
        $listingDetail = new ListingDetail();
        $listingDetail->setListing($listing);
        $listingDetail->setDefaults();

        return $listingDetail;
    }

    /**
     * Returns ListingDetail objects
     *
     * @param int $page
     * @param Criteria $c
     * @param bool $joinI18n
     * @return array
     */
    public static function getWithPager($page = 0, Criteria $c = null,
                                        $joinI18n = true)
    {
        if (!$c) {
            $c = new Criteria();
        }

        // Listing is not deleted
        $c->add(ListingPeer::LISTING_STATE_ID,
                ListingStatePeer::LISTING_STATE_ARCHIVED, Criteria::NOT_EQUAL);
        $c->addJoin(self::LISTING_ID, ListingPeer::ID, Criteria::INNER_JOIN);

        // Create pager object
        $pager = new sfPropelPager('ListingDetail', sfConfig::get('app_listing_max_page_results'));
        $pager->setCriteria($c);
        $pager->setPage($page);
        if ($joinI18n) {
            $pager->setPeerMethod('doSelectWithI18n');
            $pager->setPeerCountMethod('doCountWithI18n');
        }
        $pager->init();

        return $pager;
    }

    /**
     * Returns ListingDetail objects from Ids
     *
     * @return array
     */
    public static function getPagerFromIds($ids, $page = 0)
    {
        $c = self::createCriteriaFromIds($ids);

        return self::getWithPager($page, $c);
    }

    /**
     * Adds name criteria
     *
     * @return void
     */
    public static function doCountWithI18n(Criteria $c, $distinct = false,
                                           PropelPDO $con = null)
    {
        $c->addJoin(self::ID, ListingDetailI18nPeer::ID);
        $c->add(ListingDetailI18nPeer::CULTURE,
                sfContext::getInstance()->getUser()->getCulture());

        return self::doCount($c, $distinct, $con);
    }

    /**
     * Adds category criteria
     *
     * @return void
     */
    public static function addCategoryCriteria(Criteria $c, $categoryId)
    {
        $c->addJoin(self::LISTING_ID, ListingDetailCategoryPeer::LISTING_ID);
        $c->add(ListingDetailCategoryPeer::CATEGORY_ID, $categoryId);
    }

    /**
     * Adds usr criteria
     *
     * @return void
     */
    public static function addUsrCriteria(Criteria $c, $msisdn)
    {
        $c->addJoin(self::USR_ID, UsrPeer::ID);
        $c->add(UsrPeer::USERNAME, $msisdn);
    }

    /**
     * Creates criteria from Ids
     *
     * @return void
     */
    public static function createCriteriaFromIds($ids)
    {
        $c = new Criteria;
        $c->add(self::ID, $ids, Criteria::IN);
        $c->addAscendingOrderByColumn('idx(array[' . implode(',', $ids) . '], ' . self::ID . ')');

        return $c;
    }

    /**
     * Adds category criteria
     *
     * @return void
     */
    public static function createPublicCriteria()
    {
        $c = new Criteria();
        $c->addJoin(self::ID, ListingPeer::LIVE_LISTING_DETAIL_ID,
                    Criteria::INNER_JOIN);
        $c->add(ListingPeer::LISTING_STATE_ID,
                ListingStatePeer::LISTING_STATE_ONLINE);

        return $c;
    }

    /**
     * Adds category criteria
     *
     * @return void
     */
    public static function createUserCriteria($mobile = null)
    {
        if (!$mobile) {
            $mobile = stdUtils::getMsisdn();
        }

        $c = new Criteria();
        self::addUsrCriteria($c, $mobile);
        $c->add(self::LISTING_DETAIL_STATE_ID,
                array(
            ListingDetailStatePeer::LISTING_DETAIL_STATE_SUPERSEDED,
            ListingDetailStatePeer::LISTING_DETAIL_STATE_REJECTED,
            ), Criteria::NOT_IN);

        //Join i18n distinct
        $c->addJoin(self::ID, ListingDetailI18nPeer::ID);
        $c->add(null,
                '(((select count(*) from listing_detail_i18n where listing_detail_i18n.id = ' . self::ID
            . ') = 2 AND ' . ListingDetailI18nPeer::CULTURE . ' = \'en\') OR ((select count(*) from listing_detail_i18n where listing_detail_i18n.id = '
            . self::ID . ') = 1 ))', Criteria::CUSTOM);

        // Default order
        $c->addAscendingOrderByColumn(ListingDetailI18nPeer::NAME);

        return $c;
    }

    /**
     * Process Bulk Upload
     *
     * @param sfValidatedFile File to import from
     * @param array Unvalidated listing details container passed by referenced
     * @param array Validate listing detail container passed by referenced
     * @param
     * @return array
     */
    public static function processBulkUpload($fileObj, &$listingDetails,
                                             &$validListingDetails,
                                             $currentUser, &$batchID)
    {
        $errorValues = array();

        $tmpFile = $fileObj->getTempName();
        $data = stdData::getCsvData($tmpFile, true);

        $minLinesInArray = 3;
        if (is_array($data) && count($data) >= $minLinesInArray) {

            // Field Order
            $validCsvFields = sfConfig::get('app_valid_csv_fields');

            // dynamic Array
            $csvFieldOrder = stdData::getCsvFieldOrder($data[0], $validCsvFields);

            // if heading not found raise an error
            if (!$csvFieldOrder) {
                return array(self::ID_ERROR_HEADING_NOT_FOUND);
            }

            // remove the first line
            // (contains the column headings) ...
            unset($data[0]);

            if (count($data) > sfConfig::get('app_bulk_max_listings')) {
                return array(self::ID_ERROR_NUMBER_OF_ROWS_INVALID);
            }

            // validatorSchema
            $validatorSchema = BulkUploadHelper::filterArray($csvFieldOrder,
                                                             array('CATEGORY', 'PACKAGE'));

            // validate Csv Array
            $csvArray = BulkUploadHelper::validateCsvArray($data,
                                                           'ListingDetailCategoryPeer',
                                                           $validatorSchema,
                                                           $csvFieldOrder,
                                                           $errorValues);

            if (count($csvArray) == 0 || count($errorValues) >= sfConfig::get('app_bulk_max_errors')) {
                return $errorValues;
            }

            // extract Data into Objects
            $listingDetails = stdData::extractObjectsFromArray($data,
                                                               'ListingDetail',
                                                               $csvFieldOrder);

            // validate Objects
            $validListingDetails = self::validateListingDetails($listingDetails,
                                                                $errorValues);

            if (count($validListingDetails) == 0 || count($errorValues) > 0) {
                return $errorValues;
            }

            // generate Filename
            $fileName = BulkUploadHelper::generateFilename($fileObj,
                                                           $currentUser);

            // move file to uploads/batches folder
            if (!BulkUploadHelper::saveUploadedFile($fileObj, $fileName)) {
                return array(self::ID_ERROR_SAVE_UPLOADED_FILE);
            }

            // save to db
            $batchID = BulkUploadHelper::saveBatchToDb($fileName, $currentUser);

            if (!$batchID) {
                return array(self::ID_ERROR_SAVE_LISTING_BATCH_DB);
            }

            // save Listings
            $errorValues = self::saveListingDetails($csvArray,
                                                    $validListingDetails,
                                                    $batchID, $currentUser);

            // save Audit Log
            if (count($errorValues) == 0) {

                // update ListingBatch Statistics
                $listingBatch = ListingBatchPeer::retrieveByPK($batchID);
                $listingBatch->setStatistics(count($validListingDetails));
                $listingBatch->save();

                AuditHelper::log(AuditHelperType::BULK_UPLOAD, $currentUser->getId(), null,
                    null,
                    array(
                    '%fileName%' => $fileObj->getOriginalName(),
                    '%fileId%' => $batchID,
                    '%listings%' => count($validListingDetails),
                    )
                );
            }
        } else {
            return array(self::ID_ERROR_DATA_NOT_FOUND);
        }

        return $errorValues;
    }

    /**
     * Approve Bulk Upload
     *
     * @param integer Listing batch id to reference the correct listing batch
     * @param array Array of listing details which will be populated because parameter is passed by reference
     * @param Usr The user who is approving the bulk upload
     * @return array
     */
    public static function approveBulkUpload($listingBatchId, &$records,
                                             $currentUser)
    {
        $errorValues = array();

        $listingdetailState = ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE;

        $records = self::approveListingDetails($listingBatchId,
                                               $listingdetailState, $currentUser);
        if ($records == null) {
            $errorValues[0] = self::ID_ERROR_APPROVE_LISTINGS;
        }

        // we do no check listings by User Id
        // cause if it didnt fail on previous then
        // user is correct one
        if (!ListingPeer::updateListings($listingBatchId, ListingStatePeer::LISTING_STATE_ONLINE)) {
            $errorValues[0] = self::ID_ERROR_APPROVE_LISTINGS;
        }

        return $errorValues;
    }

    /**
     * Delete Bulk Upload
     *
     * @param integer Listing batch id to reference the correct listing batch
     * @param array Array of listing details which will be populated because parameter is passed by reference
     * @param Usr The user who is deleting the bulk upload
     * @return array
     */
    public static function deleteBulkUpload($listingBatchId, &$records,
                                            $currentUser)
    {
        $errorValues = array();

        $listingdetailState = ListingDetailStatePeer::LISTING_DETAIL_STATE_FAILED;

        $records = self::deleteListingDetails($listingBatchId,
                                              $listingdetailState, $currentUser);
        if ($records == null) {
            $errorValues[0] = self::ID_ERROR_DELETE_LISTINGS;
        }

        // we do no check listings by User Id
        // cause if it didnt fail on previous then
        // user is correct one
        if (!ListingPeer::updateListings($listingBatchId, ListingStatePeer::LISTING_STATE_ARCHIVED)) {
            $errorValues[0] = self::ID_ERROR_DELETE_LISTINGS;
        }

        if ($records > 0) {

            // update ListingBatch
            $listingBatch = ListingBatchPeer::retrieveByPK($listingBatchId);
            $listingBatch->setDeletedAt(stdTime::utcTime(), 'UTC');
            $listingBatch->setDeletedBy($currentUser->getId());
            $listingBatch->save();

            AuditHelper::log(AuditHelperType::BULK_UPLOAD_DELETE, $currentUser->getId(),
                null, null,
                array('%listings%' => $records, '%fileId%' => $listingBatchId)
            );
        }

        return $errorValues;
    }

    /**
     * Validate ListingDetails
     *
     * @param type $listingDetails
     * @param type $errorValues
     * @return type
     */
    public static function validateListingDetails($listingDetails, &$errorValues)
    {
        $validListingDetails = array();

        foreach ($listingDetails as $lineKey => $lineValue) {

            // quit if number of errors more than in config
            if (count($errorValues) >= sfConfig::get('app_bulk_max_errors')) {
                return $errorValues;
            }

            try {
                if ($lineValue->isValid()) {
                    $validListingDetails[$lineKey] = $lineValue;
                }
            } catch (Exception $e) {
                // becase exception is sfValidatorError
                // code comes from getMessage()
                if (is_numeric($e->getMessage())) {
                    $errorValues[$lineKey] = array($e->getMessage(), null);
                } else {
                    $errorValues[$lineKey] = array(null, $e->getMessage());
                }
            }
        }

        return $validListingDetails;
    }

    /**
     * Saves Listing, Listing Category and Listing Details
     *
     * @param type $csvArrays
     * @param type $listingDetails
     * @return array Either an emty array or the lsit of error encounted
     */
    public static function saveListingDetails($csvArray, &$listingDetails, $listingBatchId, $currentUser)
    {
        $conn = Propel::getConnection();
        $errorValues = array();
        $insertedCounter = 0;
        $listingCount = count($listingDetails);

        // Create the listing records
        $listingResults = ListingPeer::createListings($listingDetails, $listingBatchId);
        if ($listingResults['error']) {

            return array($listingResults['error']);
        }

        // Create the listing detail records
        $listingDetailResults = self::createListingDetails($csvArray, $listingDetails, $listingResults['minID'],
                                                           $currentUser);
        if ($listingDetailResults['error']) {

            return array($listingDetailResults['error']);
        }

        // Create the listing channel hitrate records
        $listingChannelHitrateResults = ListingChannelHitratePeer::createListingChannelHitrates($csvArray,
                                                                                      $listingResults['minID']);
        if ($listingChannelHitrateResults['error']) {

            return array($listingChannelHitrateResults['error']);
        }

        // Create the listing detail i18n records
        $listingDetailI18nResults = ListingDetailI18nPeer::createListingDetailI18ns($listingDetails,
                                                                                    $listingDetailResults['minID']);
        if ($listingDetailI18nResults['error']) {

            return array($listingDetailI18nResults['error']);
        }

        // Create the listing category records
        $listingCategorieResults = ListingDetailCategoryPeer::createListingCategories($csvArray,
                                                                                      $listingDetailResults['minID']);
        if ($listingCategorieResults['error']) {

            return array($listingCategorieResults['error']);
        }

        return $errorValues;
    }

    /**
     * Create listings using COPY command
     *
     * @param array CSV values in an associated array
     * @param ListingDetail Object passed in by reference
     * @param integer The reserved minimum listing detail ID value to start from when assiging listing detail ids
     * @param Usr The current user who will be the owner of listing details that is created
     * @return Array Associated array that contains results of insert, either stats or error details
     */
    public static function createListingDetails($csvArray, &$listingDetails, $minListingID, $currentUser)
    {
        try {

            $connection = Propel::getConnection();
            $result = array('minID' => 0, 'maxID' => 0, 'newRecs' => 0, 'error' => '');
            $result['newRecs'] = $listingCount = count($listingDetails);
            $now = date('Y-m-d H:i:s');
            $currentListingID = $minListingID;

            // If no work to do then return
            if ($listingCount == 0) {
                return $result;
            }

            // Reserve listing detail ids
            $connection->beginTransaction();
            $result['minID'] = BulkUploadHelper::getSequenceValue('listing_detail_id_seq', $connection);
            $result['maxID'] = $result['minID'] + $listingCount;
            BulkUploadHelper::setSequenceValue('listing_detail_id_seq', $result['maxID'], $connection);
            $connection->commit();

            $columns = array(
                'id',
                'listing_id',
                'listing_detail_state_id',
                'transaction_state_id',
                'listing_detail_package_id',
                'usr_id',
                'created_at',
                'point_as_4326',
            );
            $data = array();
            $ldCount = 1;
            for ($l = $result['minID']; $l < $result['maxID']; $l++) {
                $listingDetail = $listingDetails[$ldCount];
                // Get the lon lat which is mandatory
                $lonlat = $listingDetail->getLonLat();
                $data[] = array(
                    $l,
                    $currentListingID,
                    ListingDetailStatePeer::LISTING_DETAIL_STATE_PREAPPROVED,
                    TransactionStatePeer::ID_PRE_BILLED,
                    $csvArray[$ldCount]['PACKAGE_ID'],
                    $currentUser->getId(),
                    $now,
                    'SRID=4326;POINT(' . $lonlat['longitude'] . ' ' . $lonlat['latitude'] . ')',
                );
                $ldCount++;
                $currentListingID++;
            }
            $success = stdDb::copyIntoTable('listing_detail', $columns, $data);
            if ($success !== true) {
                $result['error'] = $success;
            }
        } catch (Exception $e) {
            stdLog::log(__METHOD__ . ' ### ### Copy ID: ' . $l . ' Error: ' . $e->getMessage(), null, sfLogger::ERR);
            $result['error'] = $e->getCode() . ' ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Updates ListingDetails State
     * The reason why we use raw query here is because
     * addJoin in selectCriteria block
     * did not work with BasePeer::doUpdate method
     *
     * @param type $listingBatchId
     * @param type $listingDetailStateId
     * @param type $currentUser
     * @return integer
     */
    public static function approveListingDetails($listingBatchId, $listingDetailStateId, $currentUser)
    {
        $connection = Propel::getConnection();

        // Update state
        $query = 'UPDATE listing_detail SET listing_detail_state_id = '
            . $listingDetailStateId . ', approved_at = \'' . date('Y-m-d H:i:s', stdTime::utcTime())
            . '\', approved_by = ' . $currentUser->getId() . ', live_at = \''
            . date('Y-m-d H:i:s', stdTime::utcTime()) . '\', expires_at = \''
            . date('Y-m-d H:i:s', strtotime('+' . sfConfig::get('app_listings_lifespan') . ' days', time()))
            . '\' FROM listing WHERE listing.id = listing_detail.listing_id AND listing.listing_batch_id = '
            . $listingBatchId;

        $statement = $connection->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        // Update listing_detail_id reference
        $query = 'UPDATE listing
                SET live_listing_detail_id = listing_detail.id,
                 last_listing_detail_id = listing_detail.id
                FROM listing_detail WHERE listing.id = listing_detail.listing_id
                AND listing.listing_batch_id = ' . $listingBatchId;
        $statement = $connection->prepare($query);
        $statement->execute();

        // Update hitrate
        $query = 'UPDATE listing_channel_hitrate SET live_at = \''
            . date('Y-m-d H:i:s', stdTime::utcTime()) . '\' FROM listing 
                WHERE listing.id = listing_channel_hitrate.listing_id AND listing.listing_batch_id = '
            . $listingBatchId;

        $statement = $connection->prepare($query);
        $statement->execute();

        if ($rowCount > 0) {
            stdLog::log(__METHOD__ . ' ### ### Approved records: ' . $rowCount . ' Batch: ' . $listingBatchId);
        }

        return $rowCount;
    }

    /**
     * Updates ListingDetails State
     * The reason why we use raw query here is because
     * addJoin in selectCriteria block
     * did not work with BasePeer::doUpdate method
     *
     * @param type $listingBatchId
     * @param type $listingDetailStateId
     * @param type $currentUser
     * @return integer
     */
    public static function deleteListingDetails($listingBatchId,
                                                $listingDetailStateId,
                                                $currentUser)
    {
        $connection = Propel::getConnection();

        $query = 'UPDATE listing_detail
         SET listing_detail_state_id = ' . $listingDetailStateId . '
        FROM listing WHERE listing.id = listing_detail.listing_id
        AND listing.listing_batch_id = ' . $listingBatchId . '
        AND listing_detail.usr_id = ' . $currentUser->getId();
        $statement = $connection->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        // Update state
        $query = 'UPDATE listing
                 SET last_listing_detail_id = listing_detail.id
                FROM listing_detail WHERE listing.id = listing_detail.listing_id
                AND listing.listing_batch_id = ' . $listingBatchId . '
                AND listing_detail.usr_id = ' . $currentUser->getId();
        $statement = $connection->prepare($query);
        $statement->execute();

        if ($rowCount > 0) {
            stdLog::log(__METHOD__ . ' ### ### Deleted records: ' . $rowCount . ' Batch: ' . $listingBatchId);
        }

        return $rowCount;
    }

    /**
     * Gets ListingDetail by ListingId
     *
     * @param type $listingId
     * @return ListingDetail
     */
    public static function retrieveByListingId($listingId)
    {
        $c = new Criteria();
        $c->add(self::LISTING_ID, $listingId);
        $c->add(self::LISTING_DETAIL_STATE_ID,
                ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE);

        return self::doSelectOne($c);
    }

    /**
     * SearchListing function
     *
     * @param $parameters
     * @return array(listing, distance), $limit number of results
     */
    public static function getIdsWithDistance(Criteria $c, $lonLat, $radius,
                                              $limit = null)
    {
        if ($limit) {
            $c->setLimit($limit);
        }

        $c->addSelectColumn(self::ID);
        $c->addAsColumn('distance',
                        '(ST_Distance(ST_GeomFromText(\'POINT(' . $lonLat . ')\',4326), '
            . self::POINT_AS_4326 . ') * 100)');

        $c->add(self::LISTING_DETAIL_STATE_ID,
                ListingDetailState::LISTING_DETAIL_STATE_LIVE);
        if ('>' == $radius{0}) {
            $radius = substr($radius, 1, strlen($radius) - 1);
            $c->add(self::ID,
                    'ST_Distance(ST_GeomFromText(\'POINT(' . $lonLat . ')\',4326), '
                . self::POINT_AS_4326 . ') > ' . round($radius / 100, 2),
                                                       Criteria::CUSTOM);
        } else {
            $c->add(self::ID,
                    'ST_Distance(ST_GeomFromText(\'POINT(' . $lonLat . ')\',4326), '
                . self::POINT_AS_4326 . ') <= ' . round($radius / 100, 2),
                                                        Criteria::CUSTOM);
        }
        $c->addAscendingOrderByColumn('distance');

        // Do query
        $rs = self::doSelectStmt($c);
        $results = array();
        $distances = $rs->fetchAll();

        // we interate the values an introduce them into results array with distance
        $numDistances = count($distances);
        for ($cont = 0; $cont < $numDistances; $cont++) {
            $listingId = $distances[$cont][0];
            $results[$listingId] = round($distances[$cont][1], 2);
        }

        return $results;
    }

    /**
     * Function that returns a bounding box which will include all the listings shapes specified but parameter listings
     *
     * @param array Listings to include in bounding box
     * @return array of the min and max points of the bounding box
     */
    public static function getPgGISBBox($listings, PropelPDO $con = null)
    {
        // Check if there are any positions
        if (!count($listings)) {
            return;
        }

        // Create the where clause to include all the necessary positions
        $pSql = '';
        foreach ($listings as $listing) {

            if ($pSql) {
                $pSql .= ' OR ';
            }
            $pSql .= '( id = ' . $listing->getId() . ' )';
        }

        try {
            $con = Propel::getConnection();
            $query = 'SELECT xmin(BBOX) as xmin, ymin(BBOX) as ymin, xmax(BBOX) as xmax, ymax(BBOX) as ymax
                      FROM (
                        SELECT extent(point_as_4326)::box3d as BBOX from listing_detail WHERE ' . $pSql . ') as BBOX';
            $stmt = $con->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return new Bounds($row['xmin'], $row['ymin'], $row['xmax'], $row['ymax']);
        } catch (Exception $e) {
            stdLog::log('Pgis: Could not get bounding box for positions = ' . $pSql . ' Error: ' . $e->getMessage());
        }
    }

    /**
     * Get all non-archived
     *
     * @return array
     */
    public static function selectList(Criteria $c, $culture = null, $con = null,
                                      $joinBehavior = Criteria::LEFT_JOIN)
    {
        self::adminListCriteria($c);

        // Join i18m only if it's needed
        if (self::isI18nJoined($c)) {
            $c->addJoin(self::ID, ListingDetailI18nPeer::ID);
        }

        return self::doSelect($c, $culture, $con, $joinBehavior);
    }

    /**
     * Get all non-archived
     *
     * @return array
     */
    public static function countList(Criteria $c, $distinct = false,
                                     PropelPDO $con = null)
    {
        self::adminListCriteria($c);

        // Join i18m only if it's needed
        if (self::isI18nJoined($c)) {
            $c->addJoin(self::ID, ListingDetailI18nPeer::ID);
        }

        return self::doCount($c, $distinct, $con);
    }

    /**
     * is I18n table Joined
     *
     * @return bool
     */
    public static function isI18nJoined(Criteria $c)
    {
        return in_array('listing_detail_i18n',
                        array_keys($c->getTablesColumns()));
    }

    /**
     * Add Admin list criteria
     *
     * @return void
     */
    public static function adminListCriteria(Criteria $c)
    {
        $c->add(ListingPeer::LISTING_STATE_ID,
                ListingStatePeer::LISTING_STATE_ARCHIVED, Criteria::NOT_EQUAL);
        $c->addJoin(self::ID, ListingPeer::LAST_LISTING_DETAIL_ID,
                    Criteria::INNER_JOIN);
    }

    /**
     * Bill user for the listing based on the action that is passed in
     *
     * Funtion assumes that the user to bill is either the listingdetail.usr or the current user
     *
     * @param ListingDetail Listing detail object to bill for passed by reference
     * @param Action can be CREATE, UPDATE, APPROVE, DELETE
     * @return integer
     */
    public static function billForListing(&$listingDetail, $action,
                                          $channel = ChannelPeer::CHANNEL_WEB)
    {
        // If the listingdetail doesn't contain the user to bill then bill the current user
        $billUser = self::getBillableUser($listingDetail);

        // Default the billing results to unrequired
        $billResults = new BillingActionResult(0);

        // First control the possible actions
        if (!in_array($action, self::$actions)) {

            return $billResults;
        }

        if (self::isListingBillable($listingDetail, $action)) {

            // Decide which type of transaction to use
            $transactionTypeId = self::getTransactionIdForAction($listingDetail,
                                                                 $action);

            // If we have a transaction type id, we can now bill
            if ($transactionTypeId) {

                // Create a reference for the listing action
                $reference = substr(ucfirst(strtolower($action)) . ' listing: ' . $listingDetail->getId(),
                                                       0, 255);

                // Attempt to bill the user and get the results
                $billResults = BillingActionHelper::billingAction($billUser->getContact()->getMobile(),
                                                                  $transactionTypeId,
                                                                  $reference, 1,
                                                                  $channel);

                // If the billing worked
                if ($billResults->isResultOk()) {
                    $listingDetail->setTransactionStateId(TransactionStatePeer::ID_BILLED);
                } else {
                    $listingDetail->setTransactionStateId(TransactionStatePeer::ID_BILLING_FAILED);
                }
            }
        }

        return $billResults;
    }

    /**
     * Decides if we can bill the user given user and action being performed
     *
     * Funtion assumes that the user to bill is either the listingdetail.usr or the current user
     *
     * @param ListingDetail Listing detail object to bill for passed by reference
     * @param Action can be CREATE, UPDATE, APPROVE, EXTEND, DELETE
     * @return boolean
     */
    private static function isListingBillable(&$listingDetail, $action)
    {
        // Always allow EXTEND or DELETE actions to be billable
        if (($action == 'EXTEND') || ($action == 'DELETE')) {

            return true;
        }

        // Check Transaction State
        $trasnactionStateId = $listingDetail->getTransactionStateId();
        if ($trasnactionStateId == TransactionStatePeer::ID_BILLED
            || $trasnactionStateId == TransactionStatePeer::ID_BILLING_REQUESTED
        ) {

            return false;
        }

        // If the listingdetail doesn't contain the user to bill then bill the current user
        $billUser = self::getBillableUser($listingDetail);

        // Decide if all the conditions are met to bill
        if ($action == 'CREATE' || $action == 'UPDATE') {
            if (($billUser->isPrepay() && sfConfig::get('app_prepay_listing_charge_on_create'))
                || ($billUser->isPostpay() && sfConfig::get('app_postpay_listing_charge_on_create'))
            ) {

                return true;
            }
        }
        if ($action == 'APPROVE') {
            if (($billUser->isPrepay() && sfConfig::get('app_prepay_listing_charge_on_approval'))
                || ($billUser->isPostpay() && sfConfig::get('app_postpay_listing_charge_on_approval'))
            ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Returns the transaction code to us based on an action on a given listingDetail
     *
     * @param type $listingDetail
     * @param type $action
     * @return integer Transaction id to use for action
     */
    private static function getTransactionIdForAction(&$listingDetail, $action)
    {
        // Check if listing is Premium
        $isPremium = ($listingDetail->getListingDetailPackageId() == ListingDetailPackagePeer::LISTING_PACKAGE_PREMIUM);
        $transactionTypeId = null;

        if (($action == 'CREATE') || ($action == 'APPROVE')) {
            if ($isPremium) {
                $transactionTypeId = TransactionTypePeer::CREATE_LISTING_PREMIUM;
            } else {
                $transactionTypeId = TransactionTypePeer::CREATE_LISTING_STANDARD;
            }
        } elseif ($action == 'UPDATE') {
            if ($isPremium) {
                $transactionTypeId = TransactionTypePeer::UPDATE_LISTING_PREMIUM;
            } else {
                $transactionTypeId = TransactionTypePeer::UPDATE_LISTING_STANDARD;
            }
        } elseif ($action == 'EXTEND') {
            if ($isPremium) {
                $transactionTypeId = TransactionTypePeer::EXTEND_LISTING_PREMIUM;
            } else {
                $transactionTypeId = TransactionTypePeer::EXTEND_LISTING_STANDARD;
            }
        } elseif ($action == 'DELETE') {
            if ($isPremium) {
                $transactionTypeId = TransactionTypePeer::DELETE_LISTING_PERMIUM;
            } else {
                $transactionTypeId = TransactionTypePeer::DELETE_LISITING_STANDARD;
            }
        }

        if ($transactionTypeId) {
            // Check that the transaction type is enabled
            $transactionType = TransactionTypePeer::retrieveByPK($transactionTypeId);
            if ($transactionType && $transactionType->getEnabled()) {

                return $transactionTypeId;
            }
        }
    }

    /**
     * Returns the billable user for a listing.
     *
     * Funtion assumes that the user to bill is either the listingdetail.usr or the current user
     *
     * @param ListingDetail Listing detail object that contains a reference to the biollable user
     * @return integer
     */
    private static function getBillableUser($listingDetail)
    {
        // If the listingdetail doesn't contain the user to bill then bill the current user
        $billUser = $listingDetail->getUsr();
        if (!$billUser) {
            $billUser = sfContext::getInstance()->getUser();
        }

        return $billUser;
    }

    /**
     * Refund user for the listing charge
     *
     * Funtion assumes that the user to bill is either the listingdetail.usr or the current user
     *
     * @param ListingDetail Listing detail object to bill for passed by reference
     * @return integer
     */
    public static function refundListingCharge(ListingDetail & $listingDetail)
    {
        // Default the billing results to unrequired
        $billResults = new BillingActionResult(0);

        if ($listingDetail->getTransactionStateId() == TransactionStatePeer::ID_BILLED) {

            // If the listingdetail doesn't contain the user to bill then bill the current user
            $billUser = self::getBillableUser($listingDetail);

            // Decide which type of transaction code to use for the refund
            if ($listingDetail->getListingDetailPackageId() == ListingDetailPackagePeer::LISTING_PACKAGE_STANDARD) {
                $transactionTypeId = TransactionTypePeer::REFUND_LISTING_STANDARD;
            } elseif ($listingDetail->isListingPremium()) {
                $transactionTypeId = TransactionTypePeer::REFUND_LISTING_PREMIUM;
            } else {
                // If we don't know how to refund the listing because the package is unknown then return

                return $billResults;
            }

            // Attempt to bill the user and get the results
            $billResults = BillingActionHelper::billingAction($billUser->getContact()->getMobile(),
                                                              $transactionTypeId,
                                                              'Refund listing charge');

            // If the refund worked
            if ($billResults->isResultOk()) {
                $listingDetail->setTransactionStateId(TransactionStatePeer::ID_REFUNDED);
            } else {
                $listingDetail->setTransactionStateId(TransactionStatePeer::ID_REFUND_FAILED);
            }
        }

        return $billResults;
    }

    /**
     * Get listing detailss by BatchId
     *
     * @param integer Batch id value for which to return listing details
     * @param integer (Optional) limit for the number of listing details to return
     * @return array Listing details array
     */
    public static function getListingDetailsForBatch($batchId, $limit = null)
    {
        // Add selection criteria
        $c = new criteria();
        $c->add(ListingPeer::LISTING_BATCH_ID, $batchId);

        // If calling function has specified a limit
        if ($limit) {
            $c->setLimit($limit);
        }

        return self::doSelectJoinAll($c);
    }

    /**
     * Get listing detailss by BatchId
     *
     * @param int $listingId
     * @return ListingDetail
     */
    public static function getValidListingDetail($listingId)
    {
        $c = new Criteria();
        $c->add(self::LISTING_ID, $listingId);
        $c->add(self::LISTING_DETAIL_STATE_ID,
                array(
            ListingDetailStatePeer::LISTING_DETAIL_STATE_FAILED,
            ListingDetailStatePeer::LISTING_DETAIL_STATE_PREAPPROVED,
            ), Criteria::NOT_IN);
        $c->addDescendingOrderByColumn(self::ID);

        return self::doSelectOne($c);
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
            if ($name == 'listing_detail_state') {
                $column = self::LISTING_DETAIL_STATE_ID;
            } elseif ($name == 'usr') {
                $column = self::USR_ID;
            }
        }

        return isset($column) ? $column : parent::translateFieldName($name,
                                                                     $fromType,
                                                                     $toType);
    }

    /**
     * Resizes a listing detail logo and then saves it to a configurable sub directory path
     *
     * @param sfValidatedFile File to save
     * @return string Relative path from uploads directory
     */
    static public function saveThenResizeLogo(sfValidatedFile $fileLogo)
    {
        // Check we are passed a valid file
        if ($fileLogo) {

            // Create a unique file name
            $filePath = stdImage::createImageDirPath() . $fileLogo->getExtension();

            $absFilePath = sfConfig::get('sf_upload_dir') . sfConfig::get('app_listing_logo_folder') . $filePath;

            // Save the file
            $fileLogo->save($absFilePath);

            // Resize the file
            $errorMsg = stdImage::createthumb($absFilePath, $absFilePath,
                                              sfConfig::get('app_thumb_w'),
                                                            sfConfig::get('app_thumb_h'));

            if ($errorMsg) {
                throw new Exception(
                    sfContext::getInstance()->getI18N()->__('An error occurred uploading the image: ') . $errorMsg
                );
            }

            return $filePath;
        }

        return null;
    }

    /**
     * Send a system email message
     * 
     * @param Usr $recipientUser
     * @param integer $notificationTypeId
     * @param integer $listingBatchId
     * @return void
     */
    public static function sendEmail($recipientUser, $notificationTypeId,
                                     $listingBatchId)
    {
        if ($recipientUser->getContact()->getEmail() != null) {

            // setup
            $from = array(sfConfig::get('app_default_email') => sfConfig::get('app_app_name'));
            $addresses = array($recipientUser->getContact()->getEmail());

            $notificationId = EmailHelper::composeAndSend($from, $addresses,
                                                          'email_listings_expire',
                                                          array(
                    'listingBatchId' => $listingBatchId,
                    'user' => $recipientUser,
                    'subjectId' => $notificationTypeId,
                ));
            if ($notificationId > 0) {
                // link table
                $log = new ListingDetailNotification();
                $log->setListingBatchId($listingBatchId);
                $log->setNotificationId($notificationId);
                $log->setCreatedAt(stdTime::utcTime(), 'UTC');
                $log->save();
            }
        } else {
            stdLog::log(__METHOD__ . ' ### ### No email defined for the user: ' . $recipientUser->getUsername(),
                        null, sfLogger::ERR);
        }
    }

    /**
     * Bulk Upload Listings Notify Expire
     *
     * @param type $listingBatchId
     * @param type $listingDetailStateId
     * @param type $currentUser
     * @return integer
     */
    public static function processNotifyExpireBulkListingDetails($listingBatchId,
                                                                 $records,
                                                                 $currentUser)
    {
        $maxNotifyExpiring = ListingDetailNotificationPeer::doCountbyListingBatchId($listingBatchId);

        if ($records > 0 && $maxNotifyExpiring < sfConfig::get('app_max_notify_expiring')) {

            // Email notification
            self::sendEmail($currentUser,
                            NotificationTypePeer::NOTIFICATION_TYPE_LISTING_WILL_EXPIRE,
                            $listingBatchId);

            // Sms Notification
            CsvasActionHelper::sendSmsAndRenderText($currentUser, null,
                                                    TransactionTypePeer::NO_CHARGE,
                                                    __FUNCTION__);

            // This action is audited under system user
            AuditHelper::log(AuditHelperType::LISTING_NOTIFY_EXPIRE,
                sfConfig::get('app_application_system_usr_id'), null, null,
                              array('%listings%' => $records, '%fileId%' => $listingBatchId)
            );

            stdLog::log(__METHOD__ . ' ### ### Notify Expire records: ' . $records . ' Batch: ' . $listingBatchId);
        } else {
            stdLog::log(__METHOD__ . ' ### Error: no records or max notify expiring reached',
                        null, sfLogger::ERR);
        }

        return $records;
    }

    /**
     * Bulk Upload Listings Expiration Handling
     *
     * @param type $listingBatchId
     * @param type $listingDetailStateId
     * @param type $currentUser
     * @return integer
     */
    public static function processExpiredBulkListingDetails($listingBatchId,
                                                            $limitDate,
                                                            $records,
                                                            $currentUser)
    {
        $connection = Propel::getConnection();
        $connection->beginTransaction();

        $listingDetailStateId = ListingDetailStatePeer::LISTING_DETAIL_STATE_EXPIRED;

        // We update listingDetails state
        $query = 'UPDATE listing_detail
         SET listing_detail_state_id = ' . $listingDetailStateId . '
        FROM listing WHERE listing.id = listing_detail.listing_id
        AND listing_detail.listing_detail_state_id=' . ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE . '
        AND listing.listing_batch_id = ' . $listingBatchId . '
        AND listing_detail.expires_at <= \'' . $limitDate . '\'
        AND listing_detail.usr_id = ' . $currentUser->getId();
        $statement = $connection->prepare($query);
        $statement->execute();
        $rowCount = $statement->rowCount();

        if ($rowCount > 0 && $rowCount == $records) {

            // commit UPDATE
            $connection->commit();

            // We update listing state
            $listingStateId = ListingStatePeer::LISTING_STATE_OFFLINE;

            $query = 'UPDATE listing
                 SET listing_state_id = ' . $listingStateId . '
                FROM listing_detail WHERE listing.id = listing_detail.listing_id
                AND listing_detail.listing_detail_state_id=' . ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE . '
                AND listing.listing_batch_id = ' . $listingBatchId . '
                AND listing_detail.expires_at <= \'' . $limitDate . '\'
                AND listing_detail.usr_id = ' . $currentUser->getId();
            $statement = $connection->prepare($query);
            $statement->execute();

            // Email notification
            self::sendEmail($currentUser,
                            NotificationTypePeer::NOTIFICATION_TYPE_LISTING_HAS_EXPIRED,
                            $listingBatchId);

            // Sms Notification
            CsvasActionHelper::sendSmsAndRenderText($currentUser, null,
                                                    TransactionTypePeer::NO_CHARGE,
                                                    __FUNCTION__);

            // This action is audited under system user
            AuditHelper::log(AuditHelperType::LISTING_EXPIRE,
                sfConfig::get('app_application_system_usr_id'), null, null,
                              array('%listings%' => $rowCount, '%fileId%' => $listingBatchId)
            );

            stdLog::log(__METHOD__ . ' ### ### Expired records: ' . $rowCount . ' Batch: ' . $listingBatchId);
        } else {
            $connection->rollback();
            stdLog::log(__METHOD__ . ' ### Error: rowCount: ' . $rowCount . ' not same as records: ' . $records,
                        null, sfLogger::ERR);
        }

        return $rowCount;
    }

    /**
     * Get expired listing details
     * Grouped by Batch_id
     *
     * @return array
     */
    public static function getExpiredListings($limitDate)
    {
        $connection = Propel::getConnection();

        $query = 'SELECT listing.listing_batch_id, count(listing.id) records, NULL last_listing_detail_id,
                         listing_detail.usr_id FROM listing_detail
                INNER JOIN listing ON (listing_detail.listing_id=listing.id)
                WHERE listing_detail.listing_detail_state_id=' . ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE . '
                        AND listing_detail.expires_at <= \'' . $limitDate . '\'
                        GROUP BY listing.listing_batch_id, listing_detail.usr_id
                        HAVING listing_batch_id IS NOT NULL
                        UNION ALL
                        SELECT listing.listing_batch_id, count(listing.id) records,
                               listing.last_listing_detail_id, listing_detail.usr_id
                               FROM listing_detail
                INNER JOIN listing ON (listing_detail.listing_id=listing.id)
                WHERE listing_detail.listing_detail_state_id=' . ListingDetailStatePeer::LISTING_DETAIL_STATE_LIVE . '
                        AND listing_detail.expires_at <= \'' . $limitDate . '\'
                        GROUP BY listing.listing_batch_id, last_listing_detail_id, listing_detail.usr_id
                        HAVING listing_batch_id IS NULL';

        $statement = $connection->prepare($query);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add Admin list criteria
     *
     * @return void
     */
    public static function includeSubcategoriesCriteria(Criteria $c, $categoryId)
    {
        $c2 = new Criteria();
        $c2->add(CategoryPeer::ID, $categoryId);
        $c2->addOr(CategoryPeer::PARENT_ID, $categoryId);
        $subCategories = array();
        $categories = CategoryPeer::doSelect($c2);
        foreach ($categories as $category) {
            $subCategories[] = $category->getId();
        }

        $c->addJoin(ListingDetailCategoryPeer::CATEGORY_ID, CategoryPeer::ID);
        $c->addOr(ListingDetailCategoryPeer::CATEGORY_ID, $subCategories,
                  Criteria::IN);
        $c->addOr(CategoryPeer::PARENT_ID, $subCategories, Criteria::IN);
    }

    /**
     * Get's users listing
     *
     * @param int $id
     * @param int $usr
     * @return ListingDetail
     */
    public static function getUsrListingByPK($id, $usrId = null)
    {
        $c = self::createUserCriteria();
        $c->add(self::ID, $id);

        // Override usr
        if ($usrId) {
            $c->add(self::USR_ID, $usrId);
        }

        return self::doSelectOne($c);
    }

}