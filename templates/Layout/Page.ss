<% if $ShowSidebar %>
	<div class="sidebar-layout">
		<section class="sidebar-layout--left">
		    <h1>$Title</h1>
		    $Content
		</section>
		<section class="sidebar-layout--right">
		    <% include PageSidebar %>
		</section>
	</div>
<% else %>
    <h1>$Title</h1>
    $Content
<% end_if %>