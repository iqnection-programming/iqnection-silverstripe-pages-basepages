<div class="print_me typography">
    <div id="directions_head">
    	<a name="directions_top"></a>
        <p><strong>Your Address:</strong> $StartAddress</p>
        <p><strong>Destination:</strong> $EndAddress</p>
        <p><strong>Driving Distance:</strong> $Distance</p>
        <p><strong>Driving Time:</strong> $Duration</p>
        <ul id="directions_links">
            <li><a href="$GoogleLink" class="icon_link" target="_blank"><img src="$resourceURL('iqnection-pages/basepages:images/icons_google.png')" />View on Google Maps</a></li>
            <li><a href="$PrintLink" class="icon_link" target="_blank"><img src="$resourceURL('iqnection-pages/basepages:images/icons_printer_gray.png')" />Print these Directions</a></li>
        </ul>
    </div><!--directions_head-->
    <div id="directions_list">
        $Steps.RAW
    </div><!--directions_list-->
</div><!--print_me-->