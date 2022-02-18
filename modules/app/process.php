<?php defined('BASEPATH') OR exit('no direct script access allowed');

$posturl = $_POST['posturl'];

$postid = false;
if (preg_match("/ft_id=([0-9]{9})\d+/", $posturl, $match)) {
	$postid = str_replace('ft_id=', '', $match[0]);
}elseif (preg_match("/fbid=([0-9]{9})\d+/", $posturl, $match)) {
	$postid = str_replace('fbid=', '', $match[0]);
}elseif (preg_match("/permalink\/([0-9]{9})\d+/", $posturl, $match)) {
	$postid = str_replace('permalink/', '', $match[0]);
}elseif (preg_match("/multi_permalinks=([0-9]{9})\d+/", $posturl, $match)) {
	$postid = str_replace('multi_permalinks=', '', $match[0]);
}else{	
	preg_match("/[^\/|\.!=][0-9]{7,}(?!.*[0-9]{7,})\d+/", $posturl, $match);
	if (count($match) < 1) {
		$_SESSION['message'] = ["PostID not Found, are you sure this valid ?",'danger'];
		header("Location: ./");
	}

	$postid = $match[0];
}

if ($postid) {

	require "library/FaceCoReader.php";

	$fb = new FaceCoReader([
		'cookie' => $_SESSION['login']['cookie']
	]);

	// get post info
	$postinfo = $fb->ReadPost($postid);
	if (!$postinfo['status']) {
		$_SESSION['message'] = [$postinfo['response'],'danger'];
		header("Location: ./");
	}	

	set_time_limit(0); 
	$deep = false;
	$prevdeep = false;
	$comments_data = array();
	do {

		$data = $fb->ReadComment($postid,$deep);

		if (!$data['status']) {
			continue;
		}

		// check deep
		if ($data['deep'] !== false AND $prevdeep !== false) {
			// parse deep 
			$parse = parse_url($data['deep'], PHP_URL_QUERY);
			parse_str($parse, $query_deep);
			// parse prevdeep
			$parse = parse_url($prevdeep, PHP_URL_QUERY);
			parse_str($parse, $query_prevdeep);
			// if same > break if will loop
			if ($query_deep['p'] == $query_prevdeep['p']) {
				break;
			}
		}

		$comments_data = array_merge($comments_data,$data['response']);

		if ($data['deep'] !== null) {
			$deep = $data['deep'];
			$prevdeep = $data['deep'];
		}else{
			$deep = false;
		}

		sleep(rand(4,8));

	} while ($deep !== false);

	$all_comments = array();
	foreach ($comments_data as $key => $comment) {

		if ($comment['reply_url']) {

			$deep = false;
			$reply_comments = array();
			do {

				$data = $fb->ReadCommentReply($comment['reply_url'],$deep);

				if (!$data['status']) {
					continue;
				}

				$reply_comments = array_merge($reply_comments,$data['response']);

				if ($data['deep'] !== null) {
					$deep = $data['deep'];
				}else{
					$deep = false;
				}

				sleep(rand(4,8));

			} while ($deep !== false);

			$all_comments[] = [
				'userurl' => $comment['userurl'],
				'username' => $comment['username'],
				'commentid' => $comment['commentid'],
				'message' => $comment['message'],
				'media' => $comment['media'],
				'reply' => $reply_comments
			];

		}else{
			$all_comments[] = [
				'userurl' => $comment['userurl'],
				'username' => $comment['username'],
				'commentid' => $comment['commentid'],
				'message' => $comment['message'],
				'media' => $comment['media'],
				'reply' => false
			];
		}
	}

	set_time_limit(30);

	if (count($all_comments) < 0) {
		$_SESSION['message'] = ["No Comment to display",'info'];
	}else{
		$_SESSION['message'] = ["Show Comments from post <a href='https://fb.com/{$postid}' target='_blank'>{$postid}</a>",'info'];

		// build json
		$save = [
		'post' => $postinfo['response'],
		'comments' => $all_comments
		];

		file_put_contents("./storage/comments/{$postid}.json", json_encode($save,JSON_PRETTY_PRINT));
		$_SESSION['postid'] = $postid;
	}
	header("Location: ./");
}