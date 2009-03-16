<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
		<p class="category">
			<h1><xsl:value-of select="//Category/name"/></h1>
				<xsl:for-each select="//Forum">
			<p>
				<a><xsl:attribute name="href">?action=listTopics&amp;forumID=<xsl:value-of select="id"/></xsl:attribute><xsl:value-of select="name"/></a>
			</p>
				</xsl:for-each>
		</p>
</xsl:template>
</xsl:stylesheet>