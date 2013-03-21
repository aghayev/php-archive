<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Texts </title>
 </head>
 <body>

  <table width="50%" border="0" cellspacing="0" cellpadding="4">
   <?php
   	if ($this->texts) {
   	foreach ($this->texts as $text) {
   	?>
   	<tr align="center" bgcolor="#999999">
   	<td colspan="3" style="font-size: 160%; font-family: sans-serif">
   	<?php echo $text['id']; ?>
   	</td>
   	<td colspan="3" style="font-size: 160%; font-family: sans-serif">
   	<?php echo $text['text']; ?>
   	</td>
   	</tr>
   <?php
   	}
   }
   ?>
  </table>

</body>
</html>
