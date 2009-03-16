<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
	<xsl:for-each select="objects/object">
		<p class="category">
			<a><xsl:attribute name="href">?action=listForums&amp;categoryID=<xsl:value-of select="Category/id"/></xsl:attribute><xsl:value-of select="Category/name"/></a>
		</p>
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>