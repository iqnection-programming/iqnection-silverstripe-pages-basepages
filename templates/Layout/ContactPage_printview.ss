<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Printing Driving Directions</title>
<link rel="stylesheet" type="text/css" href="/themes/mysite/css/base.css" />
<link rel="stylesheet" type="text/css" href="/themes/mysite/css/layout.css" />
<link rel="stylesheet" type="text/css" href="/themes/mysite/css/typography.css" />
<script type="text/javascript" language="javascript">setTimeout( function(){ window.print(); }, 1000);</script> 
</head>

<body bgcolor="#FFFFFF" style="background:none !important; filter:none !important;">
    <div class="print_me typography" style="padding:15px;">
        <div id="directions_head">
            <p><strong>Your Address:</strong> $StartAddress</p>
            <p><strong>Destination:</strong> $EndAddress</p>
            <p><strong>Driving Distance:</strong> $Distance</p>
            <p><strong>Driving Time:</strong> $Duration</p>
        </div><!--directions_head-->
        <div id="directions_list">
            $Steps
        </div><!--directions_list-->
    </div><!--print_me-->   
</body>
</html>
