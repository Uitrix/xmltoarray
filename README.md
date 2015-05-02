XmlConverter
=========
Small class converting xml to associative array
This class is based on the work of Lalit Patel (original code: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array/) and is restructured and functionalities are fixed a bit.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Add the following to the repository section of your composer.json:
```
  {
    "type": "vcs",
    "url": "git://github.com/Uitrix/yii2-xmltoarray.git"
  }
```
and the following to the require section of the composer.json:
```
"uitrix/yii2-xmltoarray": "*"
```

Usage
-----

Once the extension is installed, use it like the following:

export namespace:
```
use uitrix\xmltoarray\XmlConverter;
```

then:
```
$xml = <<<xml
<?xml version="1.0" encoding="UTF-8"?>
<note>
	<to>Tove</to>
	<from>Jani</from>
	<heading>Reminder</heading>
	<body>Don't forget me <hl>this weekend!</hl></body>
</note>
xml;
		$conv = new XmlConverter($xml);
		$array = $conv->createArray();
		var_export($array);
```

Result of the execution of such a code will be:
```
array (
  'note' => 
  array (
    'to' => 'Tove',
    'from' => 'Jani',
    'heading' => 'Reminder',
    'body' => 'Don\'t forget me <hl>this weekend!</hl>',
  ),
)
```
