<?php

/**
 * The myTestMappingsHelper
 * 
 * This class manipulate mappings (fixtures)
 * 
 * Fixture default mappings are the following
 * 0 is Enabled Default user
 * 1 is Enabled Registered Friend
 *
 * @author     Imran Aghayev
 * @version    $Id$
 */
class myTestMappingsHelper
{
    const TEST_MAPPINGS_CONFIG_YML_PATH = 'config/test_mappings.yml';

    /**
     * Apply Offset
     * Now if we're in the offset mode, check for a number on the line and add offset to it
     *
     * @param myTestMappingsResult $config
     * @return type
     */
    public static function applyInOffset(myTestMappingsResult $config)
    {
        $configFilePath = sfConfig::get('sf_plugins_dir') . '/myPlugin/' . self::TEST_MAPPINGS_CONFIG_YML_PATH;

        if ($config->getInOffset()) {

            // Active
            if ($config->hasActiveNicknames()) {
                foreach ($config->getActiveNicknames() as $nicknameKey => $nicknameValue) {
                    $pieces = explode('nickname', $nicknameValue);
                    $newVal = $pieces[1] + $config->getInOffset();
                    $config->setActiveNickname($nicknameKey,
                                               $pieces[0] . 'nickname' . $newVal);
                }
            }

            if ($config->hasActiveMsisdns()) {
                foreach ($config->getActiveMsisdns() as $msisdnKey => $msisdnValue) {
                    $config->setActiveMsisdn($msisdnKey,
                                             $msisdnValue + $config->getInOffset());
                }
            }

            // Reserve
            if ($config->hasReserveNicknames()) {
                foreach ($config->getReserveNicknames() as $nicknameKey => $nicknameValue) {
                    $pieces = explode('nickname', $nicknameValue);
                    $newVal = $pieces[1] + $config->getInOffset();
                    $config->setReserveNickname($nicknameKey,
                                                $pieces[0] . 'nickname' . $newVal);
                }
            }

            if ($config->hasReserveMsisdns()) {
                foreach ($config->getReserveMsisdns() as $msisdnKey => $msisdnValue) {
                    $config->setReserveMsisdn($msisdnKey,
                                              $msisdnValue + $config->getInOffset());
                }
            }
        }

        // Write Config
        $dumper = new sfYamlDumper();

        // Plain readable Yaml format
        $yaml = $dumper->dump($config->getAll(), 2);
        file_put_contents($configFilePath, $yaml);

        return $config;
    }

    /**
     * Get Mappings
     * 
     * @param type $applyOffset
     * @return myTestMappingsResult 
     */
    public static function getMappings()
    {
        $configFilePath = sfConfig::get('sf_plugins_dir') . '/myPlugin/' . self::TEST_MAPPINGS_CONFIG_YML_PATH;

        $config = sfYaml::load($configFilePath);

        return new myTestMappingsResult($config);
    }

    /**
     *  Get Mms Mappings
     */
    public static function getMmsMappings()
    {
        // get mms fixture
        $mappings = myTestMappingsHelper::getMappings();

        $mms = $mappings->getActiveMms();

        $imageFileName = sfConfig::get('sf_plugins_dir') . '/myPlugin/' . $mms['payload'];
        $imgbinary = fread(fopen($imageFileName, "r"), filesize($imageFileName));

        return array('mimetype' => $mms['mimetype'], 'payload' => base64_encode($imgbinary));
    }

}