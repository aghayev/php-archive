<?php

/**
 * Actions
 *
 * @author     Imran Aghayev
 * @version    $Id$
 */
class positionActions extends sfActions
{

    /**
     * Executes Kmlexport action
     *
     * @param sfRequest $request A request object
     * @return a kml
     */
    public function executeKmlexport(sfWebRequest $request)
    {
        $gUser = $this->getUser()->getGuardUser();

        // Set the request time out to 2 minutes
        set_time_limit(120);

        // Get the user Map Data Request object stored in session
        $mapName = $request->getParameter('mapname', 'History');
        $mdr = $this->getUser()->getMDR($mapName);
        $i18n = $this->getContext()->getI18N();
        $dt = new stdTime();

        // Add Filters
        $efDates = $mdr->getEventFilterDates('UTC');
        $dtStart = new stdTime($efDates['start']);
        $dtEnd = new stdTime($efDates['end']);
        $targets = $mdr->getTargetIds();

        // Get the user Map Data Request object stored in session
        $i18N = $this->getContext()->getI18N();

        $connection = Propel::getConnection();

        // The position.rgeo AS position_rgeo
        $query = '
            SELECT
             target.id,
             CASE WHEN position.id=target.last_position_id THEN true ELSE false END,
             target.name,
             position.id AS position_id,
             position.time AS position_time,
             position.speed AS position_speed,
             ASTEXT(position.point_as_4326) AS position,
             position.alarmed AS position_alarmed,
             (SELECT rgeo FROM position_i18n
             WHERE id=position.id and culture=\'' . $gUser->getLocalee()->getLanguage() . '\') as rgeo
             FROM position position
             LEFT JOIN target ON target.id = position.target_id
             WHERE
             position.created_at > to_timestamp(\'' . $dtStart->dump() . '\',\'YYYY-MM-DD HH24:MI:SS\')
             AND position.created_at < to_timestamp(\'' . $dtEnd->dump() . '\',\'YYYY-MM-DD HH24:MI:SS\')
             AND position.position_state_id = ' . PositionStatePeer::ID_PROCESSED . '
             AND position.target_id in (' . implode(',', $targets) . ')
             ORDER BY name, position_time';

        $statement = $connection->prepare($query);
        $statement->execute();

        $csKmlWriter = new csKmlWriter();
        $params['template'] = $i18N->__('TARGET HISTORY LOCATION BETWEEN [%s] AND [%s]');
        $params['start'] = $dtStart->dump();
        $params['end'] = $dtEnd->dump();

        // Append Name
        $csKmlWriter->appendName($params);

        // Append Style
        $csKmlWriter->appendStyle($targets);

        $counter = 1;
        while ($row = $statement->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

            // Name
            $placeParams['target_id'] = $row[0];
            $placeParams['last_position'] = $row[1];
            $placeParams['name'] = $row[2];

            // Counter
            $placeParams['index'] = $counter++;
            if ($placeParams['last_position'] == true) {
                $counter = 1;
            }

            // Lon and Lat parameters
            $points = explode(' ', $row[6]);
            $placeParams['coordinates'] = str_replace('POINT(', '', $points[0]) . ',' . str_replace(')', '', $points[1]) . ',0';

            // Description
            $position = PositionPeer::retrieveByPK($row[3]);

            // If getAlarmed
            unset($description);
            if ($row[7] == 1 && is_object($position)) {
                $alarms = $position->getAlarms();
                foreach ($alarms as $alarm) {
                    $description[] = $i18N->__('Alarm Name:') . ' ' . $alarm->getName();
                    $description[] = $i18N->__('Alarm Type:') . ' ' . $alarm->getAlarmType();
                    $description[] = $i18N->__('Alarm Time:') . ' ' . date('d/m/Y H:i:s', $row[4]);
                    $icon = $alarm->getAlarmTypeId();
                }
            }
            else {
                $speed = $row[5] ? $row[5] : '0';
                $description[] = $i18N->__('Time:') . ' ' . date('d/m/Y H:i:s', $row[4]);
            }
            $placeParams['description'] = join("\n", $description);

            $csKmlWriter->appendPlaceToFolder($placeParams);
        }

        // This function must return a csv
        $this->getResponse()->setHttpHeader('Content-Type', 'application/vnd.google-earth.kml+xml');
        $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename=' .
            strtolower(preg_replace('/ /', '_', $gUser->getCompany()->getName())) . '_data.kml');

        return $this->renderText($csKmlWriter->generate());
    }

}
