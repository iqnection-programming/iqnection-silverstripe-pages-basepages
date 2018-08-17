<% if $SidebarContent %>
	<div id="sidebar-layout">
		<section id="page_left">
		    <h1>$Title</h1>
		    $Content
		    <% include Page_columns %>
		</section>
		<section id="page_right">
		    $SidebarContent
		</section>
	</div>
<% else %>
    <h1>$Title</h1>
    $Content
    <% include Page_columns %>
<% end_if %>