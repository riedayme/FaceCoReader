<?php defined('BASEPATH') OR exit('no direct script access allowed');?>
<div class="row justify-content-center">
	<div class="col-md-6">
		<main>		

			<?php if (isset($_SESSION['message'])): ?>
				<div class="alert alert-<?php echo $_SESSION['message'][1] ?>" role="alert">
					<?php echo $_SESSION['message'][0] ?>
				</div>	
				<?php unset($_SESSION['message']); ?>
			<?php endif ?>

			<?php if (empty($_SESSION['postid'])): ?>
				<form method="POST" class="form-floating text-dark">
					<div class="form-floating mb-3">
						<input onclick="this.select()" value="" required="" type="text" class="form-control" id="username" placeholder="https://facebook.com/..." name="posturl">
						<label for="username">Insert Facebook Post URL</label>
					</div>
					<button type="submit" class="btn btn-primary">
						Extract Comment
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-left-text" viewBox="0 0 16 16">
							<path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
							<path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
						</svg>
					</button>
				</form>
			<?php else: ?>
				<?php  
				$read = file_get_contents("./storage/comments/{$_SESSION['postid']}.json");
				$decode = json_decode($read,true);				
				?>

				<div class="alert alert-primary" role="alert">
					<div class="bg-light p-2 border-radius mt-2">
						<h5 class="card-title">
							<?php echo $decode['post']['header']; ?>
						</h5>
						<p class="card-text">
							<?php echo $decode['post']['message']; ?>
							<br/>
							<?php echo str_replace('href="/', 'href="https://mbasic.facebook.com/', $decode['post']['media']); ?>							
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
								<?php echo str_replace('href="/', 'href="https://mbasic.facebook.com/', $comments['media']); ?>								
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
												<?php echo str_replace('href="/', 'href="https://mbasic.facebook.com/', $commentsR['media']);?>												
											</p>
										</div>
									</div>
								<?php endforeach ?>
							<?php endif ?>
						</div>
					</div>
				<?php endforeach ?>
			<?php endif ?>

			<?php unset($_SESSION['postid']) ?>
		</main>
	</div>
</div>