<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
		<p class="category">
			<h1><xsl:value-of select="//Category/name"/></h1>
			<p>
				<h2><xsl:value-of select="//Forum/name"/></h2>
				<xsl:for-each select="//Topic">
					<p>
						<a><xsl:attribute name="href">?action=displayTopic&amp;topicID=<xsl:value-of select="id"/></xsl:attribute><xsl:value-of select="name"/></a>
<xsl:for-each select="//Poster[id=current()/poster_id]">
by <a><xsl:attribute name="href">?action=listTopics&amp;posterID=<xsl:value-of select="id"/></xsl:attribute><xsl:value-of select="name"/></a>
</xsl:for-each>
					</p>
				</xsl:for-each>
			</p>
		</p>
</xsl:template>
</xsl:stylesheet>