# symfony2のrest的なチュートリアルという事で記載してく

## バンドル構成
* SbbsFrontBundle - アプリケーションの表示用の処理
* SbbsApiBundle - Ajax処理とかこっちでやる
* SbbsMainBundle - 実際の処理とかその辺

---


## データベースの設定

まずはデータベースの作成から。
```
CREATE DATABASE `sbbs` DEFAULT CHARACTER SET 'utf8';
```

---


## データベースへの接続設定
次に、Symfony側でMySQLに作成したデータベースへ接続する設定を行います。
ここではデータベースへ接続するユーザ名、パスワードも sbbs であると想定しています（お使いの環境に合わせて変更してください）。

app/config/parameters.ini を編集しましょう
```
parameters:
    database_driver: pdo_mysql
    database_host: 127.0.0.1
    database_port: null
    database_name: sbbs
    database_user: sbbs
    database_password: sbbs
```


---


## マイグレーションの設定
今回はマイグレーションを利用してデータベースを管理していきます。
ということでマイグレーション用のバンドルを用意しましょう。

composer.jsonに以下を追記します
```
    "require": {
		...

			  "doctrine/migrations": "dev-master",
			  "doctrine/doctrine-migrations-bundle": "dev-master"
    },
```

その後は以下のコマンドを実行してください。
```
$ php composer.phar update
```
あとはapp/AppKernel.phpに以下のように記載すればおk
```
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

	    new Doctrine\Bundle\MigrationsBundle(),
```

---



## バンドルの作成を行う
```
 php app/console generate:bundle --namespace=Sbbs/MainBundle --format=yml
 php app/console generate:bundle --namespace=Sbbs/FrontBundle --format=yml
 php app/console generate:bundle --namespace=Sbbs/ApiBundle --format=yml
```

# エンティティとかテーブルを作成する
以下のコマンドを実行してエンティティを作成します。
```
$ app/console generate:doctrine:entity --entity=SbbsMainBundle:Post --format=annotation --fields="title:string(255) body:text createdAt:datetime updatedAt:datetime deletedAt:datetime"
```

で、ここからが重要。
今回はマイグレーションで管理するので以下のコマンドを実行して、マイグレーション用のファイルを作成します。
```
$ app/console doctrine:migrations:diff
```
これで、app/DoctrineMigrationsのなかにファイルが出来ていると思います。
中をみるとこんな感じかと思います。シンプルでわかり易いですよね。
```
<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130920023015 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE Post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL, deletedAt DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("DROP TABLE Post");
    }
}
```

そして、これをデータベースに反映していきましょう。

```
$ app/console doctrine:migrations:migreate
```

これだけでテーブルの反映ができます。
本当に作られてるか確認すると

```
mysql> show tables;
+--------------------+
| Tables_in_sbbs     |
+--------------------+
| Post               |
| migration_versions |
+--------------------+
2 rows in set (0.00 sec)
```
ちゃんと作れてますね。
migration_versionsテーブルはマイグレーション管理用のテーブルになるので消さないように注意してください。

---


## ブログ閲覧ページの作成
さっそくページの作成をしていきましょう。

1. ルーティングの作成
ルーティングに関してはアノテーションを使用していこうと思っています。
app/config/routing.ymlを編集しましょう。
resourceの部分にコントローラを指定します、あとtypeをannotaionにしてください。
```
sbbs_front:
    resource: "@SbbsFrontBundle/Controller"
    type: annotation
    prefix:   /
```
2. ルーティングの設定をする
さて、次はコントローラ側を修正していきましょう。
src/Sbbs/FrontBundle/Controller/DefaultContoller.phpを編集します。

```
<?php

namespace Sbbs\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * Class DefaultController
 * @package Sbbs\FrontBundle\Controller
 *
 * @Route("/")
 */
class DefaultController extends Controller
{
	/**
	 * 掲示板TOPページ
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @Route("/")
	 * @Method("GET")
	 * @Template()
	 * @Cache(expires="+2 days",public=true)
	 */
	public function indexAction()
    {
			return array();
    }
}
```
useでアノテーションで必要なライブラリを指定しておきます。
あとはコントローラを適当に設定しましょう。
@Routeでルーティングのパスを指定、@MethodではGET,POST,PUT,DELETEなどのHTTPメソッドの指定をします。
@Templateは表示するテンプレートを指定します。@Cacheではcacheを設定していきます。
今回は、基本Ajax中心なアプリケーションになるのでFrontBundleは基本的にキャッシュしていく方針になります。

---


### ビューの作成
1. asseticによる管理
まず、最初に今回はstylesheetやjavascriptはasseticで管理したいと思います。
なので、app/config/config.ymlに以下のように追記しましょう。
```
assetic:
    debug:          %kernel.debug%
    use_controller: false
    bundles:        [SbbsFrontBundle]
```

2. bootstrap3の利用
今回はtwitter bootstrap3を使って見た目を調整したいと思います。
Bootstrap3からBootstrap3のファイルをダウンロードしてください。解凍後、distディレクトリの中身を
```
src/FrontBundle/Resources/public
```
というディレクトリを作って、入れましょう。

3. ベースとなるビューの作成
src/FrontBundle/Resources/Views/base.html.twig
```
<!DOCTYPE html5>
<html lang="ja">
	<head>
		<meta charset="UTF-8" />
		<title>{% block title %}{% endblock %} | Symfony2 Restful Bbs</title>
		<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
		{% block stylesheets %}
			{% stylesheets '@SbbsFrontBundle/Resources/public/css/*' %}
			<link rel="stylesheet" href="{{ asset_url }}" />
			{% endstylesheets %}
			<style>
				body { padding-top: 70px; }
			</style>
		{% endblock %}
	</head>
	<body>
		{% block header %}
			<header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav">
				<div class="container">
					<div class="navbar-header">
						<a href="#" class="navbar-brand">Bbs</a>
					</div>
					<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
						<ul class="nav navbar-nav">
							<li><a href="#" class="js-bbs-new">新規投稿</a></li>
						</ul>
					</nav>
				</div>
			</header>
		{% endblock %}

		<div class="container">
			{% block content %}
			{% endblock %}
		</div>
	</body>
</html>
```

4.  index.html.twigの作成
src/Sbbs/FrontBundle/Resources/Views/Default/index.html.twigファイルを用意します。
中身は以下のように記述してください。

```
{% extends 'SbbsFrontBundle::base.html.twig' %}
{% block title %}掲示板TOPページ{% endblock %}

{% block content %}
<h1>掲示板</h1>
<table class="table table-hover">
	<thead>
		<tr>
			<th style="width:100px;">掲示板ID</th>
			<th>タイトル</th>
			<th style="width:100px;">作成時間</th>
		</tr>
	</thead>
	<tbody id="js-bbs-list">

	</tbody>
</table>
{% endblock %}
```

あとはブラウザでアクセスしてみれば、ビューが表示されていると思います。
http://localhost/app_dev.php/

---


## Backbone.jsを利用するための下準備
jsのディレクトリもちょっと修正します。

- public/js/lib
- public/js/app

と二つのディレクトリを作成しましょう。
ついでにbootstrap.jsをpublic/js/libディレクトリに入れましょう。bootstrap-min.jsは削除してください。
Backbone.jsを使用する為にはJQuery,Underscore,json2が必要なので書くサイトから手にいれてpublic/js/libに入れてください。

- [JQuery](http://jquery.com/)
- [Backbone](http://backbonejs.org/)
- [Underscore](http://underscorejs.org/)
- [Json2](https://github.com/douglascrockford/JSON-js/blob/master/json2.js)

js/appの中にさらに以下のディレクトリを作成しましょう。

- public/js/app/model
- public/js/app/collection
- public/js/app/view

さらにファイルも用意します

- public/js/app/namespace.js

中身はこんな感じ
```
var Bbs = {
	Model: {},
	Collection: {},
	View: {}
}
```

その後base.html.twigを修正しましょう。bodyの閉じたくの直前に以下のように記述してください
```
		{% javascripts
			'@SbbsFrontBundle/Resources/public/js/lib/jquery-1.10.2.js'
			'@SbbsFrontBundle/Resources/public/js/lib/bootstrap.js'
			'@SbbsFrontBundle/Resources/public/js/lib/json2.js'
			'@SbbsFrontBundle/Resources/public/js/lib/underscore.js'
			'@SbbsFrontBundle/Resources/public/js/lib/backbone.js'
			'@SbbsFrontBundle/Resources/public/js/app/namespace.js'
			'@SbbsFrontBundle/Resources/public/js/app/model/*'
			'@SbbsFrontBundle/Resources/public/js/app/collection/*'
			'@SbbsFrontBundle/Resources/public/js/app/view/*'
		%}
			<script src="{{ asset_url }}"></script>
		{% endjavascripts %}

		{% block javascripts %}
		{% endblock %}
```

---

## Backbonejsを使った新規登録フォーム
まずはviews/Templates/bbs_new.html.twigを用意します
```
<!-- Modal -->
<div class="modal fade" id="bbsnew" tabindex="-1" role="dialog" aria-labelledby="bbsnewlLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">新規投稿</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" role="form">
					<div class="form-group">
						<label for="inputEmail1" class="col-lg-2 control-label">タイトル</label>
						<div class="col-lg-10">
							<input type="text" class="form-control" placeholder="title...">
						</div>
					</div>

					<div class="form-group">
						<label for="inputPassword1" class="col-lg-2 control-label">内容</label>
						<div class="col-lg-10">
							<textarea name="content" class="form-control" style="height:230px;"></textarea>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
```

次にviews/base.html.twigを修正しましょう。
```
{% include 'SbbsFrontBundle::Template/bbs_new.html.twig' %}
```
をbodyの閉じタグの直前に書きましょう。
あとheaderの部分も少し修正します
```
<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
	<ul class="nav navbar-nav">
		<li><a href="#bbsnew" class="js-bbs-new" data-toggle="modal">新規投稿</a></li>
	</ul>
</nav>
```

これでロードすればモーダルが表示されます。