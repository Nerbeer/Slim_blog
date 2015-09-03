<?php
class Article extends Model
{
	public static function add($title, $author, $summary, $content, $timestamp) {
		if($title != "" and $author != "" and $summary != "" and $content != "") {
			$article 			= Model::factory('Article')->create();
			$article->title 	= $title;
			$article->author 	= $author;
			$article->summary 	= $summary;
			$article->content 	= $content;
			$article->timestamp = $timestamp;
			$article->save();
		}  
	}

	public static function edit($id, $title,  $summary, $content, $timestamp) {
		if($title != "" and $summary != "" and $content != "") {
			$article 			= Model::factory('Article')->find_one($id);
			$article->title 	= $title;
			$article->summary 	= $summary;
			$article->content 	= $content;
			$article->timestamp = $timestamp;
			$article->save();
		}  
	}
}