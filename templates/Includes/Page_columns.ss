<% if ColAmount %>
	<div id="page_columns">
        <% if LeftColumn %>
            <div class="page_col cols_$ColAmount">
                $LeftColumn
            </div><!--page_col-->
        <% end_if %>
        <% if CenterColumn %>
            <div class="page_col cols_$ColAmount">
                $CenterColumn
            </div><!--page_col-->
        <% end_if %>
        <% if RightColumn %>
            <div class="page_col cols_$ColAmount">
                $RightColumn
            </div><!--page_col-->
        <% end_if %>
    </div><!--page_columns-->
<% end_if %>