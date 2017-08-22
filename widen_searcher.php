<?php
	$searchQuery = "";
	
	if(isset($_POST['searchQuery']))
	{
		$searchQuery = $_POST['searchQuery'];
	}
    ?>

<div id="searcher" class="pull-left">

<form id="widen_searcher" name="widen_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

<input type="hidden" name="startPos" value="0" />

<div class="input-append">
<input type="text" id="searchQuery" name="searchQuery" value="<?php echo($searchQuery); ?>" />
<input type="submit" value="Search" class="btn btn-primary" />
</div>

</form>
</div>