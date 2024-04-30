<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace INTERMediator\Message;

/**
 *
 */
class MessageStrings_ja extends MessageStrings
{
    /**
     * @var array|string[]
     */
    public array $messages = array(
        1 => 'レコード番号',
        2 => '更新',
        3 => 'レコード追加',
        4 => 'レコード削除',
        5 => '追加',
        6 => '削除',
        7 => '保存',
        8 => 'ログインユーザー: ',
        9 => 'ログアウト',
        10 => "",
        11 => "ページ目へ",
        12 => '詳細',
        13 => '一覧表示',
        14 => '複製',
        15 => 'レコード複製',
        1001 => "他のユーザーによってこのフィールドの値が変更された可能性があります。\n\n初期値=@1@\n変更値=@2@\n現在のデータベース上の値=@3@\n\nOKボタンをクリックすれば、変更値を保存します。",
        1002 => "テーブル名を決定できません: @1@",
        1003 => "更新に必要な情報が残されていません: フィールド名=@1@",
        1005 => "db_query関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1006 => "リンクノードの設定に正しくないものがあります：@1@",
        1007 => "db_update関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1008 => "db_update関数の呼び出しで、必須のプロパティ'conditions'が指定されていません",
        1009 => "",
        1010 => "",
        1011 => "db_update関数の呼び出しで、必須のプロパティ'dataset'が指定されていません",
        1012 => "クエリーアクセス: ",
        1013 => "更新アクセス: ",
        1004 => "db_query関数での通信時のエラー=@1@/@2@",
        1014 => "db_update関数での通信時のエラー=@1@/@2@",
        1015 => "db_delete関数での通信時のエラー=@1@/@2@",
        1016 => "db_createRecord関数での通信時のエラー=@1@/@2@",
        1017 => "削除アクセス: ",
        1018 => "新規レコードアクセス: ",
        1019 => "db_delete関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1020 => "db_delete関数の呼び出しで、必須のプロパティ'conditions'が指定されていません",
        1021 => "db_createRecord関数の呼び出しで、必須のプロパティ'name'が指定されていません",
        1022 => 'ご使用のWebブラウザには対応していません',
        1023 => '[このサイトはINTER-Mediatorを利用して構築しています。]',
        1024 => '複数のレコードが更新される可能性があります。keyフィールドの指定は適切でないかもしれません。そのまま進めてかまいませんか? ',
        1025 => 'レコードを本当に削除していいですか?',
        1026 => 'レコードを本当に作成していいですか?',
        1027 => "チャレンジ取得: ",
        1028 => "get_challenge関数での通信エラー=@1@/@2@",
        1029 => "パスワード変更アクセス: ",
        1030 => "パスワード変更時の通信時のエラー=@1@/@2@",
        1031 => "ファイルアップロード: ",
        1032 => "ファイルアップロード時の通信時のエラー=@1@",
        1033 => "ページファイルに指定したフィールド名「@1@」は、指定したコンテキストには存在しません",
        1034 => "他のユーザーによってこのフィールドの値が変更された可能性があります。\n\n@1@\n\nOKボタンをクリックすれば、変更値を保存します。",
        1035 => "フィールド=@1@, 初期値=@2@, 更新値=@3@\n",
        1036 => "フィールド=@1@, 式=@2@: パースエラーが発生しました。",
        1037 => "循環参照を検出しました。",
        1040 => "コンテキスト「@1@」のフィールド「@2@」はテーブルには存在しません。",
        1041 => "本当にこのレコードの複製を行いますか?",
        1042 => "このデータベースクラスはaggregation-select/from/group-byをサポートしていません。",
        1043 => "aggregation-selectとaggregation-fromの両方が必要です。いずれかの設定がコンテキスト定義にありません。",
        1044 => "aggregation-select/from/group-byを指定したコンテキストへの書き込みや更新はできません。読み出しのみです。",
        1045 => "コンテキスト「@1@」に書き込み処理をする場合は、コンテキスト定義にkeyキーの指定が必要です。",
        1046 => "ページファイル内のターゲット指定にあるコンテキスト名「@1@」に対応するコンテキストが、定義ファイルで定義されていません。",
        1047 => "ページファイル内の次のターゲット指定は、異なるコンテキスト「@1@」を使用するためバインドされません：@2@",
        1048 => "クレデンシャル取得認証: ",
        1049 => "getCredential関数での通信エラー=@1@/@2@",
        1050 => "未送信メールの宛先: ",
        1051 => "メール送信エラー: ",
        1052 => "合計",
        1053 => "クライアント間同期登録解除",
        1054 => "unregister関数での通信時のエラー=@1@/@2@",
        1055 => "Slack送信エラー: ",
        1056 => "メンテナンスコール",
        1057 => "2FA取得認証: ",
        1058 => "getCredential関数での通信エラー=@1@/@2@",
        2001 => '認証エラー!',
        2002 => 'ユーザー名:',
        2003 => 'パスワード:',
        2004 => 'ログイン',
        2005 => 'パスワード変更',
        2006 => '新パスワード:',
        2007 => 'ユーザー名、新旧パスワードのいずれかが指定されていません',
        2008 => 'サーバーとの通信に問題があります。パスワードは変更できませんでした',
        2009 => 'パスワードの変更に成功しました。新しいパスワードでログインをしてください',
        2011 => 'ユーザー名(メールアドレス):',
        2010 => 'パスワードの変更に失敗しました。旧パスワードが違うなどが考えられます',
        2012 => 'ユーザー名とパスワードを確認して、もう一度ログインをしてください',
        2013 => 'ユーザー名ないしはパスワードが入力されていません',
        2014 => 'OAuth認証',
        2015 => 'パスワードにアルファベットが含まれている必要があります',
        2016 => 'パスワードに数字が含まれている必要があります',
        2017 => 'パスワードに大文字のアルファベットが含まれている必要があります',
        2018 => 'パスワードに小文字のアルファベットが含まれている必要があります',
        2019 => 'パスワードに記号類が含まれている必要があります',
        2020 => 'パスワードはユーザー名と異なる必要があります',
        2021 => 'パスワードは@1@文字以上である必要があります',
        2022 => 'ユーザー登録をする(要メールアドレス)',
        2023 => 'パスワードをリセット',
        2024 => 'メールアドレスが必要です',
        2026 => 'SAML認証',
        2027 => "使用しているアカウントではログインできません。一度ログアウトしますか?",
        2028 => '送られてきたコード:',
        2029 => '認証',
        2030 => '登録してあるメールアドレスにコードを送りました。そのコードを入力してください。',
        2031 => 'コードを入力してください。もしくはコードの桁数が違います。',
        2032 => '入力したコードが違います。',
        3101 => "アップロードするファイルを\nドラッグ&ドロップする",
        3102 => 'ドラッグしたファイル: ',
        3201 => "ポスト可能なデータの最大値を超えました。", //設定はphp.iniファイルのpost_max_sizeです。
        3202 => "ファイルはアップロードされていません。アップロード可能なファイルの最大サイズを超えたなどの理由がありえます。",
        3203 => "アップロード可能なファイルの最大サイズを超えました。", //設定はphp.iniファイルのupload_max_filesizeです。
        3204 => "アップロードされたのはファイルの一部分です。",
        3205 => "テンポラリーディレクトリがありません。",
        3206 => "ディスクもしくはファイルシステムに書き込みができませんでした。",
        3207 => "拡張モジュールがファイルのアップロードを停止しました。",
        3208 => "ファイルアップロード時の不明なエラーが発生しました。",
        3209 => "ファイル選択...",
        3210 => "選択ファイル：",
        3211 => "アップロード",
        3212 => "ファイルが壊れているためファイルのアップロードに失敗しました。",
        9999 => "For testing to customize this message",
    );
}
