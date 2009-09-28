<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
		<p class="forum">
			<h1><xsl:value-of select="//Forum/name"/></h1>
			<p>
				<h2><xsl:value-of select="//Topic/name"/></h2>
				<xsl:for-each select="//Post">
					<p>
						<p>
						<xsl:for-each select="//PostText[id=current()/id]">
						<xsl:choose>
						<xsl:when test="name!=''">
							<h3><xsl:value-of select="name"/></h3>
							by 
						</xsl:when>
						<xsl:otherwise>
							<h3>Answer</h3>
							from 
						</xsl:otherwise>
						</xsl:choose>
						</xsl:for-each>
							<xsl:for-each select="//Poster[id=current()/poster_id]"><xsl:value-of select="name"/><br />
</xsl:for-each>
							<xsl:for-each select="//PostText[id=current()/id]">
<xsl:value-of select="text" disable-output-escaping="yes"/>
</xsl:for-each>
						</p>
					</p>
				</xsl:for-each>
			</p>
		</p>
</xsl:template>
</xsl:stylesheet>