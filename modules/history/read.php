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
			</div>

			<?php foreach ($decode as $comments): ?>
				<div class="card text-dark mb-2">
					<div class="card-body">
						<h5 class="card-title">
							<?php echo $comments['username']; ?>
						</h5>
						<p class="card-text">
							<?php echo $comments['message']; ?>
							<br/>
							<?php echo $comments['media']; ?>
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
											<?php echo $commentsR['media']; ?>
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