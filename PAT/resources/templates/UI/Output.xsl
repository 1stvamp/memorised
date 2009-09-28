<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
		<p>
			<h1>Output topic</h1>
			<p>
				<xsl:value-of select="//message"/><br />
				
						<xsl:if test="//done!=1">
							<form method="post" action="index.php">
							<input type="hidden" name="topicID">
							<xsl:attribute name="value"><xsl:value-of select="//id"/></xsl:attribute>
							</input>
							<input type="hidden" name="action" value="output" />
							<input type="text" name="question">
							<xsl:attribute name="value"><xsl:value-of select="//question"/></xsl:attribute>
							<input type="submit" />
							</input>
							</form>
						</xsl:if>
			</p>
		</p>
</xsl:template>
</xsl:stylesheet>