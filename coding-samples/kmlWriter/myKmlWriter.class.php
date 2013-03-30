<?php

/**
 * Exporer to Google Earth
 *
 * @author     Imran Aghayev
 * @version    $Id$
 */
class myKmlWriter
{
    protected $schemaDom;
    protected $kmlNode;
    protected $documentNode;
    protected $folderNode;
    protected $pathBuffer = array();
    protected $colorBuffer = array();
    protected $colorStack = array();

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        // Creates the Document
        $this->schemaDom = new DomDocument('1.0', 'UTF-8');
        $this->schemaDom->formatOutput = true;
        $this->schemaDom->preserveWhiteSpace = true;

        // Creates the root KML element and appends it to the root document
        $this->kmlNode = $this->schemaDom->createElementNS('http://earth.google.com/kml/2.1', 'kml');
        $this->schemaDom->appendChild($this->kmlNode);

        // Creates a KML Document element and append it to the KML element
        $this->documentNode = $this->schemaDom->createElement('Document');

        $this->colorBuffer[1] = '64eeee17';
        $this->colorBuffer[2] = '7d0000ff';
        $this->colorBuffer[3] = 'afff0000';
        $this->colorBuffer[4] = '87000000';
        $this->colorBuffer[5] = 'ff0000ff';
        $this->colorBuffer[6] = '7f00ffff';
        $this->colorBuffer[7] = '7dff0000';
        $this->colorBuffer[8] = '7d00ff00';
        $this->colorBuffer[9] = '7d00ffff';
        $this->colorBuffer[10] = 'fff00ff';
    }

    /**
     * This getRandomColor
     *
     * @return string
     */
    public function getRandomColor()
    {
        if (empty($this->colorStack)) {
            $this->colorStack = $this->colorBuffer;
        }

        return $fruit = array_shift($this->colorStack);
    }

    /**
     * This appendFolder
     *
     * @return void
     */
    public function appendFolder($params)
    {
        $this->folderNode = $this->schemaDom->createElement('Folder');

        // Name Parameter
        $nameNode = $this->schemaDom->createElement('name');

        $nameCDATANode = $this->schemaDom->createCDATASection(html_entity_decode($params['name']));

        $nameNode->appendChild($nameCDATANode);

        $this->folderNode->appendChild($nameNode);
    }

    /**
     * This appendPlaceToFolder
     *
     * @return void
     */
    public function appendPlaceToFolder($params)
    {
        // add Folder
        if (!$this->folderNode) {
            $this->appendFolder($params);
        }

        $this->appendPlace($params);

        if ($params['last_position'] == true) {
            $this->appendPath($params['target_id']);
            unset($this->folderNode);
        }
    }

    /**
     * This appendName
     *
     * @return void
     */
    public function appendName($params)
    {
        // add Name
        $nameNode = $this->schemaDom->createElement('name', sprintf($params['template'],
                    $params['start'], $params['end']));
        $this->documentNode->appendChild($nameNode);
    }

    /**
     * This appendStyle
     *
     * @return void
     */
    public function appendStyle($targets)
    {
        $styleNode1 = $this->generateStyle($this->schemaDom, 'waypoint');
        $this->documentNode->appendChild($styleNode1);

        foreach ($targets as $targetName => $targetValue) {
            // Creates the three Style elements
            // and append the elements to the Document element
            $styleNode1 = $this->generateStyle($this->schemaDom, 'track' . $targetValue);
            $this->documentNode->appendChild($styleNode1);
        }
    }

    /**
     * This appendPlace
     *
     * @return void
     */
    public function appendPlace($params)
    {
        $placemarkNode = $this->schemaDom->createElement('Placemark');

        // Name Parameter
        $nameNode = $this->schemaDom->createElement('name');

        $nameVar = $params['name'] . ' (' . $params['index'] . ')';

        $nameCDATANode = $this->schemaDom->createCDATASection(html_entity_decode($nameVar));

        $nameNode->appendChild($nameCDATANode);

        // Description Parameter
        $descriptionNode = $this->schemaDom->createElement('description');

        $descriptionCDATANode = $this->schemaDom->createCDATASection(html_entity_decode($params['description']));

        $descriptionNode->appendChild($descriptionCDATANode);

        // StyleUrl Parameter
        if ($params['last_position'] == true) {
            $styleUrlNode = $this->schemaDom->createElement('styleUrl', '#track' . $params['target_id']);
        } else {
            $styleUrlNode = $this->schemaDom->createElement('styleUrl', '#waypoint');
        }

        // Point Parameter
        $pointNode = $this->schemaDom->createElement('Point');

        // Coordinates Parameter
        $coordinatesNode = $this->schemaDom->createElement('coordinates', $params['coordinates']);

        //Compilation
        $pointNode->appendChild($coordinatesNode);

        $placemarkNode->appendChild($nameNode);
        $placemarkNode->appendChild($descriptionNode);
        $placemarkNode->appendChild($styleUrlNode);
        $placemarkNode->appendChild($pointNode);

        $this->folderNode->appendChild($placemarkNode);

        // Save for Path
        $this->pathBuffer[$params['target_id']][] = $params['coordinates'];
    }

    /**
     * This appendPath
     *
     * @return void
     */
    public function appendPath($targetId)
    {
        if (isset($this->pathBuffer[$targetId])) {
            $styleName = 'track' . $targetId;

            $placemarkNode = $this->schemaDom->createElement('Placemark');

            // Name Parameter
            $nameNode = $this->schemaDom->createElement('name', 'Path');

            // StyleUrl Parameter
            $styleUrlNode = $this->schemaDom->createElement('styleUrl', '#' . $styleName);

            // LineString Parameter
            $lineStringNode = $this->schemaDom->createElement('LineString');

            // Coordinates Parameter
            $lineString = '';
            foreach ($this->pathBuffer[$targetId] as $pathBufferString) {
                $lineString .= $pathBufferString . "\n";
            }

            $coordinatesNode = $this->schemaDom->createElement('coordinates', $lineString);

            //Compilation
            $lineStringNode->appendChild($coordinatesNode);

            $placemarkNode->appendChild($nameNode);
            $placemarkNode->appendChild($styleUrlNode);
            $placemarkNode->appendChild($lineStringNode);

            $this->folderNode->appendChild($placemarkNode);

            $this->documentNode->appendChild($this->folderNode);
        }
    }

    /**
     * This generateStyle
     *
     * @return array
     */
    private function generateStyle($schemaDom, $styleName)
    {
        $styleNode = $schemaDom->createElement('Style');
        $styleNode->setAttribute('id', $styleName);

        // Scale Parameter
        $scaleNode = $schemaDom->createElement('scale', '0.9');

        // IconStyle
        $iconStyleNode = $schemaDom->createElement('IconStyle');

        $iconNode = $schemaDom->createElement('Icon');

        if ($styleName == 'waypoint') {
            $hrefNode = $schemaDom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal4/icon61.png');
        } else {
            $hrefNode = $schemaDom->createElement('href', 'http://maps.google.com/mapfiles/kml/pal4/icon62.png');
        }

        // Icon Shape
        $xNode = $schemaDom->createElement('x', '64');
        $yNode = $schemaDom->createElement('y', '128');
        $wNode = $schemaDom->createElement('w', '32');
        $hNode = $schemaDom->createElement('h', '32');

        // Compile Icon
        $iconNode->appendChild($hrefNode);
        $iconNode->appendChild($xNode);
        $iconNode->appendChild($yNode);
        $iconNode->appendChild($wNode);
        $iconNode->appendChild($hNode);

        // Compile IconStyle
        $iconStyleNode->appendChild($scaleNode);
        $iconStyleNode->appendChild($iconNode);

        // LabelStyle
        $labelStyleNode = $schemaDom->createElement('LabelStyle');

        $colorNode = $schemaDom->createElement('color', 'ff00ff0c');

        // Compile LabelStyle
        $labelStyleNode->appendChild($scaleNode);
        $labelStyleNode->appendChild($colorNode);

        // Compile LineStyle
        if (preg_match('/^track/', $styleName)) {
            $lineStyleNode = $schemaDom->createElement('LineStyle');

            $colorNode = $schemaDom->createElement('color', $this->getRandomColor());

            $widthNode = $schemaDom->createElement('width', '5');

            // Compile LineStyle
            $lineStyleNode->appendChild($widthNode);
            $lineStyleNode->appendChild($colorNode);

            $styleNode->appendChild($lineStyleNode);
        }

        // Compile Style
        $styleNode->appendChild($labelStyleNode);
        $styleNode->appendChild($iconStyleNode);

        return $styleNode;
    }

    /**
     * The generate
     *
     * @return array
     */
    public function generate()
    {
        // add Placemark
        //And then append it to the databaseNode node
        $this->kmlNode->appendChild($this->documentNode);

        return $this->schemaDom->saveXML();
    }

}
