<section id="form_page_left">
    <h1>$Title</h1>
    $Content
    <% if ContactPageLocations %>
        <div id="map_wrap"><div id="map_canvas"></div></div>
        <% if MapDirections %>
            <div id="directions_wrap">
                <form id="frmDD" onsubmit="getDirections();return false;">
                    <% if NeedLocationsSelect %>
                        <div class="field text">
                            <label class="left">Destination:</label>
                            <div class="middleColumn">
                                <select name="to_address" id="to_address" class="select">
                                    <% control ContactPageLocations %>
                                        <option value="$Address">{$Title}: $Address</option>
                                    <% end_control %>
                                </select>                            	
                            </div>
                        </div>
                    <% else %>
                        <input type="hidden" name="to_address" id="to_address" readonly value="<% control ContactPageLocations.First %>{$Title}: $Address<% end_control %>" />
                    <% end_if %>
                    <div class="field text">
                        <label class="left">Get Directions:</label>
                        <div class="middleColumn">
                            <input type="text" value="" name="from_address" id="from_address" class="text">
                        </div>
                    </div>
                    <div class="Actions">
                        <input type="submit" value="Go">
                    </div>
                    <div class="clear"></div>
                </form>
                <div id="directions_ajax">
                </div><!--directions_ajax-->
            </div><!--directions_wrap-->
        <% end_if %>
    <% end_if %>
</section>
<section id="form_page_right">
    $RenderForm
</section>