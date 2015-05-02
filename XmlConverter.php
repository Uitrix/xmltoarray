<?php

namespace uitrix\xmltoarray;

use DOMDocument;
use DOMElement;

class XmlConverterException extends \Exception
{

}

/**
 * Class XmlConverter
 * Small class to convert XML to array.
 *
 * Based on the following class: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array/
 * Original author: Lalit Patel
 *
 * It is restructured and have some additional fixes.
 *
 * @package uitrix\xmltoarray
 */
class XmlConverter
{

	private $xml;
	private $encoding;

	/**
	 * Initializes the converter
	 *
	 * @param string|DOMDocument $inputXml
	 * @param $version
	 * @param $encoding
	 * @param $formatOutput
	 */
	public function __construct($inputXml, $version = '1.0', $encoding = 'UTF-8', $formatOutput = true)
	{
		$this->xml = new DOMDocument($version, $encoding);
		$this->xml->formatOutput = $formatOutput;
		$this->encoding = $encoding;

		$xml = &$this->xml;
		if (is_string($inputXml))
		{
			$parsed = $xml->loadXML($inputXml);
			if (!$parsed)
			{
				throw new XmlConverterException('Error parsing the XML string.');
			}
		}
		else
		{
			if (get_class($inputXml) != 'DOMDocument')
			{
				throw new XmlConverterException('The input XML object should be of type: DOMDocument.');
			}
			$xml = $inputXml;
		}
	}

	/**
	 * Convert an XML to Array
	 *
	 * @return array
	 * @throws XmlConverterException
	 */
	public function createArray()
	{
		$xml = &$this->xml;
		$array[$xml->documentElement->tagName] = self::convert($xml->documentElement);

		return $array;
	}

	/**
	 * Convert an Array to XML
	 *
	 * @param DOMDocument|DOMElement $node
	 * @return string|array
	 */
	private static function convert($node)
	{
		$output = [];

		switch ($node->nodeType)
		{
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;

			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;

			case XML_ELEMENT_NODE:
				// If there're mixed child nodes (like with #text and other ones in one time)
				// we would like to collect them all as string.
				// therefor first we need to determine whether we have such a case
				$hasText = false;
				$hasNotext = false;
				$innerXml = '';
				for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++)
				{
					$child = $node->childNodes->item($i);
					if ($child->nodeName == '#text')
					{
						if (trim($child->nodeValue) != '')
						{
							$hasText = true;
						}
					}
					else
					{
						$hasNotext = true;
					}

					$innerXml .= $child->ownerDocument->saveXML($child);
				}

				if ($hasText && $hasNotext)
				{
					$output = $innerXml;
					break;
				}

				// for each child node, call the covert function recursively
				for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++)
				{
					$child = $node->childNodes->item($i);
					$v = self::convert($child);
					if (isset($child->tagName))
					{
						$t = $child->tagName;

						// assume more nodes of same kind are coming
						if (!isset($output[$t]))
						{
							$output[$t] = [];
						}
						$output[$t][] = $v;
					}
					else
					{
						//check if it is not an empty text node
						if ($v !== '')
						{
							$output = $v;
						}
					}
				}

				if (is_array($output))
				{
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ($output as $t => $v)
					{
						if (is_array($v) && count($v) == 1)
						{
							$output[$t] = $v[0];
						}
					}
					if (empty($output))
					{
						//for empty nodes
						$output = '';
					}
				}

				// loop through the attributes and collect them
				if ($node->attributes->length)
				{
					$a = [];
					foreach ($node->attributes as $attrName => $attrNode)
					{
						$a[$attrName] = (string)$attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if (!is_array($output))
					{
						$output = ['@value' => $output];
					}
					$output['@attributes'] = $a;
				}
				break;
		}

		return $output;
	}

}