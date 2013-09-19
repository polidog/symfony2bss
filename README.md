symfony2のrest的なチュートリアルという事で記載してく

# バンドル構成
* SbbsFrontBundle - アプリケーションの表示用の処理
* SbbsApiBundle - Ajax処理とかこっちでやる
* SbbsMainBundle - 実際の処理とかその辺

# データベースの設定

まずはデータベースの作成から。
```
CREATE DATABASE `sbbs` DEFAULT CHARACTER SET 'utf8';
```

# データベースへの接続設定
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


# マイグレーションの設定
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


# バンドルの作成を行う
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