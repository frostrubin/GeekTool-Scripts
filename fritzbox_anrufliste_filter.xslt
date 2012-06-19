<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="xml" encoding="utf-8" indent="yes"/>

<!-- / forward slash is used to denote a patern that matches the root node of the XML document -->
<xsl:template match ="/" >
  <html>
    <head>
      <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css' />
      <style type="text/css">
	  body { margin:  0; 
                 padding: 0; 
                 border:  0; 
                 text-align:center; 
                 background-color: #F7F8F9;
		 background: #F5F6F7 url(http://bitnugget.de/gradient.jpeg) repeat-x 0 0;
		 font-family: 'Ubuntu', sans-serif;
                 color: black;}
	  h1, h3 { font-weight: 400; 
                   font-size: 40px;}
	  h3 {font-size: 20px;margin-top: -10px;}
	  table {margin:auto;}
	   <!--CallIn-->
	   .type1 { background-image: url(data:image/gif;base64,R0lGODlhEAAQAOZJADiL3jqN4XOauWqUtQtXl9fg5gZRjwhUkgNNiQpWlTeK3HacujiK3VKEqiFwuCNyvBFdnx9opyJxuzaI2hBcnrPI1qi/0aW9zzGC0tHc4xVblit8yTWH2EyAqDGD0xFXj9Te5WeStB9qqxRhpbnM2RdemRZdmDWH2UR5pBZcl0F3os7a4i+B0ApSjSl5xpCvxhFYkrbK13CYuG2Wtg5amyVlmjSG1yx8ysXU3g1ZmoGlv5Oxxyh4wwlUk16MsCV0vw9cnTqM4HygvQZQjiFwuhtinxNZkcLS3TuO4gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAEkALAAAAAAQABAAAAeUgEmCg4SFhodHR4RHF4gaRYokDTkjAoNGSUcpCiw1Rw08SAAHFoItESUKNkaKPj9ISDcqgjCwJx+KSTgEAbA0giawIrmCAxJIGyiCRszMhBkIGEFAC4eEOwYcDASW1oI6CQwAECFDHRXWCwQTAQ8ALgbdhkIHHrCiFDHWLwgOvQE9QHgrMCABEQcDvA1aMUNGgUGBAAA7);}
           <!-- CallInFailed -->
	   .type2 {background-image: url(data:image/gif;base64,R0lGODlhEAAQANU8APpUU/5VVbyRhKRIOLBKPMCYi+vg2vhUU7KAcptHNZRGM6NIN59HNs1ORcGaju1ST61JO9e+tcail7eJe+FRS86vpahJOaBHN97Kw9pQSc5ORZpHNOfZ082to+LRyuJRTJxHNfNTUax2ZutST9VPR9BORvxVVN3IwdW8s+PTzL+Vierd2PZUUt1QSqxJOujb1q9KO8Sekr6Th7VKPdzGvqlJObuPgtFORqt0ZLB8bbxLP/9VVQAAAAAAAAAAAAAAACH5BAEAADwALAAAAAAQABAAAAZ5QJ5wSCwaj0YPqqhr6kBCDMIyKxB1uyxBiMjsAIzIEKsVTkjZD26c3W15qUEgWxs7dUNBaUcRIYcvCiMmLg5/QhUJIQcDVocSCwcAMDYbOTRIDgMsATcALQmORjEMD20AECdIHQoNcwEXK38GAgsaDQKHQhwyKgZDQQA7);}
	   <!-- CallOut -->
	   .type3 {background-image: url(data:image/gif;base64,R0lGODlhEAAQAOZGAFjVBVfSBpq5gZO0eVWOK0uSGOHo14OpZFfRBpy6g0mGHFbMCEqOGkuRGVGwEEqLGlfRB1O6DcrZu5i3fpW2e6/Hmk+KI1fPB1K2Dr/Sr02fFaTAjWGXONTgyFTAC0yWF93l0lGzD4uvb3ahVUyYF1SSJszavrHInZGzdlbNCH6mX1aZJkyVGEqPGUyZF1XICVGxD9vk0FWVJ1aiIcjXuVO8DNLexVXHCVGyD1KPJk2aFlaaJkqKG1jUBlS/C3iiWN/n1aC9iFXGClWUJ73QrFjWBQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAEYALAAAAAAQABAAAAeUgEaCg4SFhoVDiUMrgzZEh0NFRUNGJgcsGgKERAQLkjlGBxFFAQwZgpwLEDIzFkYiGJIeI4IcQhBDjwRGHQUAkh+oOyWPhAM4RT4/g0TFhCAKNz0kCYeEJw8pCAWa1oIbDQgBOig8KjTWCQUXACEBNQ/dhkEML5KkLhLWFQoOvwAtgHgzMKABDAcDvA2KQWGCgUGBAAA7);}
 
	   .type1, .type2, .type3 {background-repeat: no-repeat; background-position:center;}
      </style>
      <title>Anrufliste</title>
    </head>
    <body>
      <h1>Anrufliste</h1>
      <h3>Stand: SETDATEANDTIME</h3>
        <xsl:apply-templates />
    </body>
  </html>
</xsl:template>

<xsl:template match="Foncalls" >
  <table width="800" border="1">
    <tr bgcolor = "#ACCFEF" color="#3F464C">
      <th>Type</th>
      <th>Date</th>
      <th>Name</th>
      <th>Number</th>
    </tr>
    <xsl:for-each select="Calls" >
      <tr>
        <td>
	  <xsl:attribute name="class">type<xsl:value-of select="Type"/></xsl:attribute>	
        </td>
        <td><xsl:value-of select="Date"/></td>
        <td><xsl:value-of select="Name"/></td>
        <td><xsl:value-of select="Number"/></td>
      </tr>
    </xsl:for-each>
  </table>
</xsl:template>
</xsl:stylesheet>
