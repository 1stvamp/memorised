<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" indent="yes"/>
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><xsl:value-of select="outputDoc/@name"/></title>
<meta http-equiv="Content-Type" content="charset=iso-8859-1" />
<meta http-equiv="Content-Language" content="en-uk" />
<meta name="MSSmartTagsPreventParsing" content="TRUE" />
<meta name="author" content="Wesley A Mason" />
<style type="text/css">
@import "templates/www/pat.css";
</style>
</head>
<body>
<xsl:value-of select="outputDoc/content" disable-output-escaping="yes"/>
</body>
</html>
</xsl:template>
</xsl:stylesheet>