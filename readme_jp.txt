$Id: readme_jp.txt,v 1.2 2008/12/21 20:49:33 ohwada Exp $

=================================================
Name:    XOOPS Language Translation Support
Version: 0.20
Date:    2008-12-22
Author:  Kenichi OHWADA
URL:     http://linux.ohwada.jp/
Email:   webmaster@ohwada.jp
=================================================

言語翻訳支援 モジュール
XOOPSモジュールの言語ファイルの翻訳を支援するため、
英語と自国語(日本語)の２つの言語の対訳表を表示・編集する。

● 主な変更
1. Ｄ３モジュールに対応した
(1) XOOPS_TRUST_PATH 側に言語ファイルがあれば、それを読み込む

(2) 言語ファイルの prefix に対応した

下記のような言語ファイルを生成するには、
modinfo.php
----
define($constpref."xxx","yyy");
----

下記のようにオプション・ファイルを設定する
options/webphoto/filter.php の例
-----
function xlang_filter_webphoto_modinfo_php_key( $str )
{
	$key = '"_MI_WEBPHOTO_' ;
	$val = '$constpref."' ;
	$str = str_replace( $key, $val, $str );
	return $str;
}
-----


=================================================
Version: 0.10
Date:    2008-01-10
=================================================

言語翻訳支援 モジュール
XOOPSモジュールの言語ファイルの翻訳を支援するため、
英語と自国語(日本語)の２つの言語の対訳表を表示・編集する。

「XOOPS 英日対訳表 作成ツール」をモジュール化したもの。
http://linux.ohwada.jp/modules/mydownloads/singlefile.php?cid=1&lid=57


● 主な機能

モジュール化にあたり、下記の機能を追加した
(1) WEB上で複数の人が単語の編集ができる
(2) 編集後にファイルとしてダウンロードできる
(3) 単語ごとの編集来歴が見れる
(4) UTF-8 環境では、文字コードの異なる複数の言語が扱える


● 初期設定

(1) include/charset-dist.php を include/charset.php にリネームする

(2) include/charset.php に下記を追記する
---
$XLANG_MY_LANGUAGE = 'japanese';
---


● 制約事項

1. 言語ファイルを PHP プログラムとして読み込みます
そのため、次のケースでは、正しく読めないことがあります
(1) 言語ファイルにプログラムの記述がある
(2) XOOPS 定数や PHP 定数などと文字列の連結を行っている
(3) 複数の言語ファイルをまとめて読むモードにて、他のファイルで定義されたのと同じ定数名がある

この制約を回避するには、下記のような対応をしてください。
(a) 事前に言語ファイルを修正する
(b) 読み込んだ後で、データベースのデータを修正する


2. 言語以外のファイル
言語ファイルのディレクトリイに、言語の定義に使用されていないプログラム・ファイルを持つモジュールがあります。
これを読み込むと、入れ物はあるが中身のないデータベース・エリアが生成されます。
管理上の混乱の元になります。

これを回避するために、読み込まないファイルを指定することが出来ます。
options/weblinks/skip_files.php の例
-----
$XLANG_SKIP_FILES = array(
	'weblinks_language_convert.php',
	'language_convert.php'
);
-----


3. 複製モジュールへの対応
weblinks など複製可能なモジュールでは、言語ファイルにプログラムの記述があります
上記の制約から 最初の defined 文は読み込まれません
-----
if( !defined('WEBLINKS_LANG_MI_LOADED') ) 
{
  define('WEBLINKS_LANG_MI_LOADED', 1);
  define("_MI_WEBLINKS_NAME", "Web Links");
}
-----

これに対応するために、言語ファイル毎に生成用のテンプレートを持つことが出来ます。
options/weblinks/modinfo.php.tpl の例
-----
if( !defined("WEBLINKS_LANG_MI_LOADED") ) 
{
{XLANG_DEFINES}
}
-----


4. エスケープ処理
単語・文章から言語ファイルを生成する場合、単語・文章の内容は下記のようにダブルコーテーション " で括られます。
-----
define("_MI_XLANG_NAME", "Language Translation Support");
-----

単語・文章に中にあるダブルコーテーションはエスケープされます。
「ダブルコーテーション " を指定する」 は
 "ダブルコーテーション \" を指定する" となる

ダブルコーテーションそのものを記述するには、ダブルコーテーションを３回記述します """
「そのままのダブルコーテーション """ を記述する」 は
 "そのままのダブルコーテーション " を記述する" となる

エスケープ処理の一覧
-----
"    =>  \"
\"   =>  \"  (元のまま)
"""  =>  "
\'   =>  '
改行コード(\n,\r) => 削除('')
タブコード(\t)    => 削除('')
-----


5. エスケープ処理のカスタマイズ
上記の標準的なエスケープ処理では、不便なことがあります。

下記のような対応をしてください。
(a) 生成されたファイルを修正する (変更が少ない場合はこれが簡単)
(b) 言語ファイル毎にエスケープ処理を定義する 

options/cbb-308/filter.php の例
-----
function xlang_filter_cbb_308_modinfo_php( $str )
{
	ここに処理を記述する
}
-----


● 将来の課題

現在は、翻訳そのものは行わない。
いくつか 翻訳の WEB サービスが出てきているので、そのうち対応してみる
http://babelfish.altavista.com/
http://www.webservicex.net/

