=== ProbonoSEO Basic ===
Contributors: probonodesign
Plugin URI: https://seo.prbn.org
Tags: seo, japanese, schema, breadcrumbs, meta tags
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

日本語サイトに最適化された、Made in Japan の総合SEOプラグイン。無料70機能 + Pro218機能 = 全288機能。

== Description ==

ProbonoSEO は、日本語サイトのために設計・開発された総合SEOプラグインです。日本語の文章構造やコンテンツパターンを前提に、ゼロから構築しました。

= ProbonoSEO を選ぶ理由 =

* **Made in Japan** - 日本のSEOを理解した日本人開発者が開発
* **無料で70機能** - 他のSEOプラグインより多い無料機能
* **日本語最適化** - 日本語のタイトル、メタディスクリプションを適切に処理
* **買い切り型** - Pro版はサブスクではなく、一度の購入で永続利用

= 無料版の機能（70機能） =

**タイトル最適化（7機能）**
基本タイトルタグ最適化、区切り文字設定、サイト名付加設定、タイトルとH1の一致チェック、カテゴリ名付加設定、タイトル重複チェック、記号・絵文字使用警告

**メタディスクリプション（7機能）**
基本メタディスクリプション最適化、本文自動抽出、キーワード自動抽出、要約自動生成、禁止語句チェック、文字数チェック、重複チェック

**canonical（5機能）**
基本canonical設定、canonical自動生成、末尾スラッシュ統一、wwwあり/なし統一、パラメータ除去

**OGP / Twitter（12機能）**
基本OGP出力、OGPタイトル設定、OGP説明文設定、OGP画像自動取得、OGPデフォルト画像設定、Facebook App ID設定、LINE対応OGP、サムネイル自動生成、画像サイズ検出、画像alt属性チェック、日本語URL対応、基本Twitterカード出力

**schema / パンくず（16機能）**
基本schema出力（Article）、基本schema出力（WebSite）、基本schema出力（WebPage）、基本schema出力（Organization）、基本schema出力（Person）、基本schema出力（BreadcrumbList）、基本schema出力（SearchAction）、基本schema出力（ImageObject）、基本パンくず出力（トップ/カテゴリ/タグ/投稿/固定ページ/アーカイブ/検索結果/404）

**内部リンク / 速度（12機能）**
前後記事リンク、同カテゴリ記事リンク、子ページリンク、関連記事リンク、タグ関連ロジック、外部リンクnofollow、カテゴリリンク形式、画像遅延読み込み、iframe遅延読み込み、CSS圧縮、JS圧縮、WordPress標準スクリプト最適化

**記事SEO（6機能）**
見出し構造チェック、画像alt属性チェック、画像数チェック、文字数チェック、カテゴリ適合チェック、タグ重複チェック

**サイト診断（3機能）**
タイトル重複診断、メタディスクリプション重複診断、表示速度診断

**その他（2機能）**
meta要素クリーンアップ、Google Search Console認証コード設定

= Pro版の機能（218機能） =

ライセンス購入（買い切り）ですべてのPro機能が解放されます。

* **AI日本語SEO補助（21機能）** - AIタイトル提案、見出し最適化、FAQ生成、コンテンツ分析
* **競合分析（12機能）** - 競合タイトル・メタディスクリプション・見出し・文字数分析
* **投稿SEO（21機能）** - 投稿ごとのSEO設定、SERPプレビュー、SNSプレビュー、SEOスコア
* **Pro専用強化（39機能）** - カスタム投稿タイプ、カスタムタクソノミー、REST API、WP-CLI、Google Search Console連携
* **リッチスニペット対応（42機能）** - FAQ、HowTo、レビュー、レシピ、イベント、商品、動画、求人、ローカルビジネス schema
* **schema高度版（50機能）** - Software、Course、Book、Movie、MusicAlbum、Podcast、他44種類のschema
* **速度改善Pro（18機能）** - WebP/AVIF変換、クリティカルCSS、フォント最適化、ページキャッシュ
* **サイト診断Pro（12機能）** - インデックスステータス、クロールエラー、Core Web Vitals、セキュリティ診断、PDFレポート
* **全体管理（3機能）** - メール通知、Slack通知、デバッグモード

全機能の詳細は [https://seo.prbn.org](https://seo.prbn.org) をご覧ください。

= 外部サービスへの接続 =

このプラグインは、特定の状況で外部サービスに接続します。

**ライセンス認証（Pro版のみ）**
Proライセンスを有効化する際、ライセンスサーバーに接続してライセンスキーを検証します。

* サービスURL: https://seo.prbn.org/api/
* 目的: ライセンスの有効化と検証
* 送信データ: ライセンスキー、サイトURL
* プライバシーポリシー: https://seo.prbn.org/privacy-policy/
* 利用規約: https://seo.prbn.org/terms/

**OpenAI API（Pro版のみ、オプション）**
AI搭載SEO機能はOpenAI APIを使用します。この機能はオプションであり、ユーザー自身のAPIキーの入力が必要です。

* サービス: OpenAI API
* 目的: AIタイトル提案、コンテンツ分析、FAQ生成
* 送信データ: 投稿コンテンツ（ユーザーがAI機能を実行した場合のみ）
* プライバシーポリシー: https://openai.com/privacy/
* 利用規約: https://openai.com/terms/

無料版は外部サービスに接続しません。

== Installation ==

1. プラグイン > 新規追加 > プラグインのアップロード からZIPファイルをアップロード
2. プラグイン一覧から「有効化」
3. 管理画面メニュー「ProbonoSEO」を開く
4. 基本SEO機能は自動で動作します
5. Pro機能を使う場合は、ライセンスタブでライセンスキーを入力

== Frequently Asked Questions ==

= 日本語サイト向けですか？ =
はい。ProbonoSEO は日本語サイト向けに設計・最適化されています。日本語のテキスト構造と日本の検索エンジン向けSEOのベストプラクティスに対応しています。

= Made in Japan ですか？ =
はい。ProbonoSEO は日本国内で、日本人開発者によって開発されています。

= 他のSEOプラグインと併用できますか？ =
可能ですが、推奨しません。ProbonoSEO は他のSEOプラグイン（Yoast、RankMath等）を自動検出し、重複出力を抑制して競合を回避します。

= どのWordPressテーマでも使えますか？ =
はい。標準的なWordPressテーマであれば問題なく動作します。高度にカスタマイズされたテーマでは、軽微な調整が必要な場合があります。

= 無料版に含まれるschemaは？ =
無料版には以下のschemaが含まれます：WebSite、WebPage、Organization、Person、Article、BreadcrumbList、SearchAction、ImageObject

= Pro機能を使うには？ =
[https://seo.prbn.org](https://seo.prbn.org) でProライセンスを購入し、ProbonoSEO > ライセンス タブでライセンスキーを入力してください。

= Pro版はサブスクですか？ =
いいえ。Proライセンスは買い切り型で、永続的なアップデートが含まれます。

= このプラグインはユーザーデータを収集しますか？ =
無料版はデータの収集や外部サービスへの接続を行いません。Pro版はライセンス検証のためにライセンスサーバーに接続し、オプションでAI機能のためにOpenAI APIに接続します。詳細は「外部サービスへの接続」セクションをご覧ください。

== Screenshots ==

1. ダッシュボード - サイトのSEO状況の概要
2. 一般設定 - 基本的なSEOオプションの設定
3. タイトル設定 - タイトルタグの動作をカスタマイズ
4. Schema設定 - 構造化データ出力の設定
5. Pro機能 - ライセンスで解放される高度な機能

== Changelog ==

= 1.5.0 =
* WordPress.org Plugin Check完全対応
* PHPCSコーディング規約準拠
* セキュリティ関連コードの改善

= 1.4.2 =
* WordPress 6.9対応
* Plugin Check対応（コード品質改善）

= 1.0.0 =
* 初回リリース
* 日本語SEO最適化のための無料70機能
* ライセンスで利用可能なPro218機能

== Upgrade Notice ==

= 1.5.0 =
WordPress.org Plugin Check完全対応およびセキュリティ改善。