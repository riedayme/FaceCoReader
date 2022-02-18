<?php defined('BASEPATH') OR exit('no direct script access allowed');
$file = strip_tags($_GET['read']);
$read = file_get_contents('./storage/comments/'.$file);
$decode = json_decode($read,true);				
?>

<div class="row justify-content-center">
	<div class="col-md-6">
		<main>	

			<div class="alert alert-primary" role="alert">
				Read history <a target="_blank" href="https://facebook.com/<?php echo explode('.', $file)[0] ?>"><?php echo $file ?></a>
				<div class="bg-light p-2 border-radius mt-2">
					<h5 class="card-title">
						<?php echo $decode['post']['header']; ?>
					</h5>
					<p class="card-text">
						<?php echo $decode['post']['message']; ?>
						<br/>
						<?php echo str_replace('href="/', 'href="https://mbasic.facebook.com/', $decode['post']['media']); 
						?>
					</p>
				</div>
			</div>

			<?php foreach ($decode['comments'] as $comments): ?>
				<div class="card text-dark mb-2">
					<div class="card-body">
						<h5 class="card-title">
							<?php echo $comments['username']; ?>
						</h5>
						<p class="card-text">
							<?php echo $comments['message']; ?>
							<br/>
							<?php echo str_replace('href="/', 'href="https://mbasic.facebook.com/', $comments['media']);
							?> 
						</p>

						<?php if ($comments['reply']): ?>
							<?php foreach ($comments['reply'] as $commentsR): ?>
								<div class="card text-dark mb-2">
									<div class="card-body">
										<h5 class="card-title">
											<?php echo $commentsR['username']; ?>
										</h5>
										<p class="card-text">
											<?php echo $commentsR['message']; ?>
											<br/>
											<?php echo str_replace('href="/', 'href="https://mbasic.facebook.com/', $commentsR['media']);
											?> 
										</p>
									</div>
								</div>
							<?php endforeach ?>
						<?php endif ?>
					</div>
				</div>
			<?php endforeach ?>

			<div class="alert alert-primary" role="alert">
				<a href="./?module=history" class="alert-link">
					&lt; Back
				</a>
			</div>

		</main>
	</div>
</div>