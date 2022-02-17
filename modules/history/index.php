<?php defined('BASEPATH') OR exit('no direct script access allowed');

$dir = './storage/comments/';
// $files = array_diff(scandir($dir,SCANDIR_SORT_DESCENDING), array('..', '.'));

$files = glob($dir.'*.json');
usort($files, function($a, $b) {
    return filemtime($b) - filemtime($a);
});
?>

<div class="row justify-content-center">
	<div class="col-md-6">
		<main>	

			<div class="row">
				<?php 
				foreach($files as $file){
					?>
					<div class="col-md-6">
						<div class="card text-dark mb-2">
							<div class="card-body">
							<h5 class="card-title text-truncate">
									<a href="./?module=history&read=<?php echo basename($file); ?>"><?php echo basename($file); ?></a>
								</h5>
								<p class="card-text">
									<?php echo date("d F Y H:i:s.", filemtime($file)) ?>
								</p>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</main>
	</div>
</div>