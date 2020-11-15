<?php

return [
    'security-warning' => 'セキュリティの警告',
    'nothing-to-delete' => '削除するアイテムはありません',

    'layouts' => [
        'my-account' => 'マイアカウント',
        'profile' => 'プロフィール',
        'address' => 'アドレス',
        'reviews' => 'レビュー',
        'wishlist' => 'お気に入り',
        'orders' => '注文',
        'downloadable-products' => 'ダウンロードアイテム'
    ],

    'common' => [
        'error' => 'エラーが発生しました。しばらく待ってから、再度アクセスしてください。'
    ],

    'home' => [
        'page-title' => config('app.name') . ' - ホーム',
        'featured-products' => 'おすすめ',
        'new-products' => 'New',
        'verify-email' => 'メールアドレスを確認します',
        'resend-verify-email' => '確認メールを再送信'
    ],

    'header' => [
        'title' => 'アカウント',
        'dropdown-text' => 'カート、注文、お気に入りの管理',
        'sign-in' => 'ログイン',
        'sign-up' => 'アカウント登録',
        'account' => 'アカウント',
        'cart' => 'カート',
        'profile' => 'プロフィール',
        'wishlist' => 'お気に入り',
        'logout' => 'ログアウト',
        'search-text' => 'アイテムを探す'
    ],

    'minicart' => [
        'view-cart' => 'カートを見る',
        'checkout' => 'レジへ進む',
        'cart' => 'カート',
        'zero' => '0'
    ],

    'footer' => [
        'subscribe-newsletter' => 'メルマガ登録',
        'subscribe' => '登録',
        'locale' => '言語',
        'currency' => '通貨',
    ],

    'subscription' => [
        'unsubscribe' => '退会',
        'subscribe' => '登録',
        'subscribed' => 'メルマガ登録が完了しました',
        'not-subscribed' => 'メルマガ登録ができません。しばらく待ってから、再度お試しください。',
        'already' => '既にメルマガ登録済です',
        'unsubscribed' => '退会しました',
        'already-unsub' => '既に退会済です',
        'not-subscribed' => 'メールを送信できませんでした。しばらくしてから再度お試しください。'
    ],

    'search' => [
        'no-results' => 'お探しの条件に合う検索結果が見つかりませんでした。',
        'page-title' => '検索',
        'found-results' => '検索結果',
        'found-result' => '検索結果',
        'analysed-keywords' => 'Analysed Keywords'
    ],

    'reviews' => [
        'title' => 'レビュー',
        'add-review-page-title' => 'レビューを書く',
        'write-review' => 'レビューを書く',
        'review-title' => 'タイトル',
        'product-review-page-title' => 'アイテムのレビュー',
        'rating-reviews' => '評価と意見',
        'submit' => '送信',
        'delete-all' => '全て削除します',
        'ratingreviews' => ':rating 評価 & :review レビュー',
        'star' => '星',
        'percentage' => ':percentage %',
        'id-star' => '星',
        'name' => '名前'
    ],

    'customer' => [
        'signup-text' => [
            'account_exists' => '既に登録されております',
            'title' => 'アカウント登録'
        ],

        'signup-form' => [
            'page-title' => 'アカウント登録',
            'title' => 'アカウント登録',
            'firstname' => '名',
            'lastname' => '姓',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'confirm_pass' => 'パスワードを確認',
            'button_title' => '登録',
            'agree' => '同意',
            'terms' => '規約',
            'conditions' => '条件',
            'using' => 'by using this website',
            'agreement' => '承諾',
            'success' => 'アカウントが登録されました',
            'success-verify' => 'アカウント登録完了の確認メールが送信されました',
            'success-verify-email-unsent' => 'アカウント登録が完了されましたが、確認メールが送信ができませんでした',
            'failed' => 'アカウント登録ができませんでした　しばらくしてから、再度お試しください',
            'already-verified' => '既にアカウントをお持ちです　確認メールを再度送信してください',
            'verification-not-sent' => '確認メールの送信中に問題が発生しました しばらくしてから、再度お試しください',
            'verification-sent' => '確認メールが送信されました',
            'verified' => 'アカウント登録が完了しました　ログインしてください',
            'verify-failed' => 'メールアドレスの確認ができませんでした',
            'dont-have-account' => 'アカウント登録がされていません',
            'customer-registration' => 'アカウント登録'
        ],

        'login-text' => [
            'no_account' => 'アカウントがありません',
            'title' => '新規アカウント登録',
        ],

        'login-form' => [
            'page-title' => 'ログイン',
            'title' => 'ログイン',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'forgot_pass' => 'パスワードをお忘れですか',
            'button_title' => 'ログイン',
            'remember' => 'ログイン状態を保持する',
            'footer' => '© Copyright :year Webkul Software, All rights reserved',
            'invalid-creds' => 'ログイン情報を確認してください',
            'verify-first' => 'メールアドレスを認証してください',
            'not-activated' => 'このアカウントは管理者の認証が出来次第、有効になります',
            'resend-verification' => 'アカウント確認メールが再送信'
        ],

        'forgot-password' => [
            'title' => 'パスワードの再発行',
            'email' => 'メールアドレス',
            'submit' => 'パスワード再発行メールを送信',
            'page_title' => 'パスワードの再発行'
        ],

        'reset-password' => [
            'title' => 'パスワードを再発行',
            'email' => '登録メールアドレス',
            'password' => 'パスワード',
            'confirm-password' => 'パスワード確認',
            'back-link-title' => '戻る',
            'submit-btn-title' => 'パスワードをリセット'
        ],

        'account' => [
            'dashboard' => 'プロフィールを編集',
            'menu' => 'メニュー',

            'profile' => [
                'index' => [
                    'page-title' => 'プロフィール',
                    'title' => 'プロフィール',
                    'edit' => '編集',
                ],

                'edit-success' => 'プロフィールが更新されました',
                'edit-fail' => 'プロフィールを更新できませんでした　しばらくしてから再度お試しください。',
                'unmatch' => 'パスワードが一致しません',

                'fname' => '名',
                'lname' => '姓',
                'gender' => '性別',
                'other' => 'その他',
                'male' => '男性',
                'female' => '女性',
                'dob' => '生年月日',
                'phone' => '電話番号',
                'email' => 'メールアドレス',
                'opassword' => '前のパスワード',
                'password' => 'パスワード',
                'cpassword' => 'パスワード確認',
                'submit' => '保存する',

                'edit-profile' => [
                    'title' => 'プロフィールの編集',
                    'page-title' => 'プロフィールの編集'
                ]
            ],

            'address' => [
                'index' => [
                    'page-title' => '住所',
                    'title' => '住所',
                    'add' => '住所を追加',
                    'edit' => '編集',
                    'empty' => '住所が登録されておりません　下のリンクから作成してください',
                    'create' => '住所を作成',
                    'delete' => '削除',
                    'make-default' => '既定の住所に設定',
                    'default' => '既定の住所',
                    'contact' => '連絡先',
                    'confirm-delete' =>  'この住所を削除しますか？',
                    'default-delete' => '既定の住所の削除ができません',
                    'enter-password' => 'パスワードを入力してください',
                ],

                'create' => [
                    'page-title' => '新規住所登録',
                    'title' => '住所を追加',
                    'street-address' => '住所',
                    'country' => '国',
                    'state' => '都道府県',
                    'select-state' => '都道府県を選択してください',
                    'city' => '市町村',
                    'postcode' => '郵便番号',
                    'phone' => '電話番号',
                    'submit' => '保存する',
                    'success' => '住所が保存されました',
                    'error' => '住所の保存に失敗しました。'
                ],

                'edit' => [
                    'page-title' => '住所を編集',
                    'title' => '住所を編集',
                    'street-address' => '住所',
                    'submit' => '保存する',
                    'success' => '住所が更新されました',
                ],
                'delete' => [
                    'success' => '住所が削除されました',
                    'failure' => '住所の削除に失敗しました。',
                    'wrong-password' => 'パスワードが正しくありません'
                ]
            ],

            'order' => [
                'index' => [
                    'page-title' => '注文一覧',
                    'title' => '注文一覧',
                    'order_id' => '注文ID',
                    'date' => '日時',
                    'status' => '状況',
                    'total' => '合計',
                    'order_number' => '注文番号'
                ],

                'view' => [
                    'page-tile' => '注文 #:order_id',
                    'info' => '詳細',
                    'placed-on' => '日時',
                    'products-ordered' => '注文内容',
                    'invoices' => '領収書',
                    'shipments' => '発送',
                    'SKU' => 'SKU',
                    'product-name' => 'アイテム番号',
                    'qty' => '数',
                    'item-status' => 'アイテム状況',
                    'item-ordered' => '注文完了 (:qty_ordered)',
                    'item-invoice' => '領収書発行済 (:qty_invoiced)',
                    'item-shipped' => '発送済 (:qty_shipped)',
                    'item-canceled' => 'キャンセル済 (:qty_canceled)',
                    'price' => '価格',
                    'total' => '合計',
                    'subtotal' => '小計',
                    'shipping-handling' => '送料',
                    'tax' => '消費税',
                    'discount' => '割引',
                    'tax-percent' => '消費税割合',
                    'tax-amount' => '消費税',
                    'discount-amount' => '割引',
                    'grand-total' => '合計',
                    'total-paid' => '合計金額',
                    'total-refunded' => '返金額',
                    'total-due' => '合計',
                    'shipping-address' => '送付先',
                    'billing-address' => '領収書宛先',
                    'shipping-method' => '配送方法',
                    'payment-method' => '支払い方法',
                    'individual-invoice' => '領収書 #:invoice_id',
                    'individual-shipment' => '出荷 #:shipment_id',
                    'print' => '印刷',
                    'invoice-id' => '領収書番号',
                    'order-id' => '注文番号',
                    'order-date' => '注文日',
                    'bill-to' => '請求先',
                    'ship-to' => '送り先',
                    'contact' => '連絡先',
                    'refunds' => '返金',
                    'individual-refund' => '返金 #:refund_id',
                    'adjustment-refund' => '返金調整',
                    'adjustment-fee' => '調整手数料',
                    'tracking-number' => '追跡番号',
                    'cancel-confirm-msg' => 'この注文をキャンセルしてもよろしいですか ?'
                ]
            ],

            'wishlist' => [
                'page-title' => 'お気に入り',
                'title' => 'お気に入り',
                'deleteall' => '全て削除',
                'moveall' => '全てのアイテムをカートへ移動する',
                'move-to-cart' => 'カートへ移動',
                'error' => 'アイテムのお気に入り追加に失敗しました。しばらくしてから再度お試し下さい。',
                'add' => 'アイテムをお気に入りに追加',
                'remove' => 'アイテムをお気に入りから削除',
                'moved' => 'アイテムをカートへ移動しました',
                'move-error' => 'アイテムのお気に入り追加に失敗しました。しばらくしてから再度お試し下さい。',
                'success' => 'アイテムをお気に入りに追加しました',
                'failure' => 'アイテムのお気に入り追加に失敗しました。しばらくしてから再度お試し下さい。',
                'already' => 'このアイテムは既にお気に入りに追加されています。',
                'removed' => 'アイテムはお気に入りから削除されました。',
                'remove-fail' => 'アイテムをお気に入りから削除することができませんでした。しばらくしてから再度お試し下さい。',
                'empty' => 'お気に入りにアイテムがありません。',
                'remove-all-success' => '全てのアイテムがお気に入りから削除されました。',
            ],

            'downloadable_products' => [
                'title' => 'ダウンロードアイテム',
                'order-id' => '注文番号',
                'date' => '日時',
                'name' => 'アイテム名',
                'status' => 'ステータス',
                'pending' => '処理中',
                'available' => 'ダウンロード可能',
                'expired' => '期限切れ',
                'remaining-downloads' => '残りのダウンロード数',
                'unlimited' => '無制限',
                'download-error' => 'ダウンロードリンクの有効期限が切れています.'
            ],

            'review' => [
                'index' => [
                    'title' => 'レビュー',
                    'page-title' => 'レビュー'
                ],

                'view' => [
                    'page-tile' => 'レビュー #:id',
                ]
            ]
        ]
    ],

    'products' => [
        'layered-nav-title' => 'Shop By',
        'price-label' => 'As low as',
        'remove-filter-link-title' => '全て削除',
        'sort-by' => '並び順',
        'from-a-z' => 'From A-Z',
        'from-z-a' => 'From Z-A',
        'newest-first' => '新着順',
        'oldest-first' => '古い順',
        'cheapest-first' => '価格が安い順',
        'expensive-first' => '価格が高い順',
        'show' => 'Show',
        'pager-info' => ':showing - :total',
        'description' => '解説',
        'specification' => '仕様',
        'total-reviews' => ':全てのレビュー',
        'total-rating' => ':total_rating Ratings & :total_reviews Reviews',
        'by' => 'By :name',
        'up-sell-title' => 'お客様におすすめのアイテム',
        'related-product-title' => '関連アイテム',
        'cross-sell-title' => 'Más opciones',
        'reviews-title' => 'レビュー',
        'write-review-btn' => '評価を書いてください',
        'choose-option' => 'オプションを選択',
        'sale' => 'セール',
        'new' => 'new',
        'empty' => 'このカテゴリーにアイテムがありません。',
        'add-to-cart' => 'カートに追加する',
        'book-now' => '今予約する',
        'buy-now' => '今すぐ購入',
        'whoops' => 'すみません！',
        'quantity' => '数量',
        'in-stock' => '在庫あり',
        'out-of-stock' => '在庫なし',
        'view-all' => '全て見る',
        'select-above-options' => '最初に上記のオプションを選択してください.',
        'less-quantity' => '数量１未満は選択できません.',
        'available-for-order' => '注文可能',
        'settings' => 'Settings',
        'compare_options' => 'Compare Options',
    ],

    'buynow' => [
        'no-options' => 'このアイテムを購入される前にオプションを選択してください'
    ],

    'checkout' => [
        'cart' => [
            'integrity' => [
                'missing_fields' =>'赤くなった項目を入力してください',
                'missing_options' => 'Options are missing for this product.',
                'missing_links' => 'Downloadable links are missing for this product.',
                'qty_missing' => 'Atleast one product should have more than 1 quantity.',
                'qty_impossible' => 'Cannot add more than one of these products to cart.'
            ],
            'create-error' => 'カートで問題が発生しました',
            'title' => 'カート',
            'empty' => 'カートが空です。',
            'update-cart' => 'カートを更新する',
            'continue-shopping' => '買い物を続ける',
            'proceed-to-checkout' => '購入手続きに進む',
            'remove' => '削除',
            'remove-link' => '削除',
            'move-to-wishlist' => 'お気に入りに移動する',
            'move-to-wishlist-success' => 'アイテムがお気に入りに追加されました。',
            'move-to-wishlist-error' => 'アイテムをお気に入りに追加することができませんでした。しばらくしてから再度お試し下さい。',
            'add-config-warning' => 'カートに追加する前にオプションを選択してください',
            'quantity' => [
                'quantity' => '数量',
                'success' => 'カートが更新されました。',
                'illegal' => '数量を1以下にすることは出来ません。',
                'inventory_warning' => 'ご希望の数量の在庫が現在ございません。しばらくしてから再度お試し下さい。現在在庫がございません。',
                'error' => 'アイテムの更新が出来ませんでした。しばらくしてから再度お試し下さい。'
            ],
            'item' => [
                'error_remove' => 'カートに削除するアイテムがございません。',
                'success' => 'アイテムがカートに追加されました。',
                'success-remove' => 'アイテムがカートから削除されました。',
                'error-add' => 'アイテムをカートに追加できません。しばらくしてから再度お試し下さい。',
            ],
            'quantity-error' => 'ご希望の数量の在庫が現在ございません。',
            'cart-subtotal' => '小計',
            'cart-remove-action' => '手続きを進めますか。',
            'partial-cart-update' => 'Only some of the product(s) were updated',
            'link-missing' => ''
        ],

        'onepage' => [
            'title' => 'レジ',
            'information' => '詳細',
            'shipping' => '発送',
            'payment' => '支払い',
            'complete' => '完了',
            'review' => 'レビュー',
            'billing-address' => '領収書宛先',
            'sign-in' => 'ログイン',
            'first-name' => '名',
            'last-name' => '姓',
            'email' => 'メールアドレス',
            'address1' => '住所',
            'city' => '市町村',
            'state' => '都道府県',
            'select-state' => '地域を選択　都道府県　市町村',
            'postcode' => '郵便番号',
            'phone' => '電話番号',
            'country' => '国',
            'order-summary' => '注文内容',
            'shipping-address' => '送付先住所',
            'use_for_shipping' => 'この住所に送る',
            'continue' => '続ける',
            'shipping-method' => '発送方法を選択',
            'payment-methods' => '支払い方法を選択',
            'payment-method' => '支払い方法',
            'summary' => '注文内容',
            'price' => '金額',
            'quantity' => '数量',
            'billing-address' => '領収書宛先',
            'shipping-address' => '送付先',
            'contact' => '連絡先',
            'place-order' => '注文を確定する',
            'new-address' => '新しい住所を追加する',
            'save_as_address' => '住所を保存',
            'apply-coupon' => 'クーポンを使用する',
            'amt-payable' => 'お支払い金額',
            'got' => 'Got',
            'free' => '無料',
            'coupon-used' => '使用済クーポン',
            'applied' => '適用されました',
            'back' => '戻る',
            'cash-desc' => '現金支払い',
            'money-desc' => '銀行振り込み',
            'paypal-desc' => 'Paypal',
            'free-desc' => '送料無料',
            'flat-desc' => '送料一律'
        ],

        'total' => [
            'order-summary' => '注文を確定する',
            'sub-total' => 'アイテム',
            'grand-total' => '合計',
            'delivery-charges' => '送料',
            'tax' => '税',
            'discount' => '割引',
            'price' => '金額',
            'disc-amount' => '割引額',
            'new-grand-total' => '合計',
            'coupon' => 'クーポン',
            'coupon-applied' => '使用済みクーポン',
            'remove-coupon' => 'クーポンを削除',
            'cannot-apply-coupon' => 'クーポンを使用することができません'
        ],

        'success' => [
            'title' => '注文が正しく完了しました',
            'thanks' => 'ご注文ありがとうございます',
            'order-id-info' => 'お客様の注文番号 #:order_id',
            'info' => 'お客様のご注文詳細と追跡情報をメールアドレスに送ります'
        ]
    ],

    'mail' => [
        'order' => [
            'subject' => 'ご注文ありがとうございます',
            'heading' => 'ご注文の受付が完了しました。',
            'dear' => ':customer_name様',
            'dear-admin' => ':admin_name様',
            'greeting' => 'この度は当ショップでお買い物いただきありがとうございます。 お客様注文番号 :order_id',
            'greeting-admin' => '注文番号 :order_id placed on :created_at',
            'summary' => '注文内容',
            'shipping-address' => '送付先住所',
            'billing-address' => '領収書宛先',
            'contact' => '連絡先',
            'shipping' => '配送方法',
            'payment' => '支払い方法',
            'price' => '金額',
            'quantity' => '数量',
            'subtotal' => '小計',
            'shipping-handling' => '送料',
            'tax' => '税',
            'discount' => '割引',
            'grand-total' => '合計',
            'final-summary' => '発送手続き完了後、お知らせメールを配信いたしますので、今しばらくお待ちください。',
            'help' => 'お問合せなどは下記メールアドレスへご連絡ください。:support_email',
            'thanks' => 'Gracias!',
            'cancel' => [
                'subject' => '注文がキャンセルされました',
                'heading' => '注文がキャンセルされました',
                'dear' => ':customer_name様',
                'greeting' => '注文 #:order_idはキャンセルされました。',
                'summary' => '注文内容',
                'shipping-address' => '送付先住所',
                'billing-address' => '領収書宛先',
                'contact' => '連絡先',
                'shipping' => '配送方法',
                'payment' => '支払い方法',
                'subtotal' => '小計',
                'shipping-handling' => '送料',
                'tax' => '税',
                'discount' => '割引',
                'grand-total' => '合計',
                'final-summary' => '私たちのお店にお越しいただき、ありがとうございます。',
                'help' => 'お問合せなどは下記メールアドレスへご連絡ください。 :support_email',
                'thanks' => 'Gracias!',
            ]
        ],
        'invoice' => [
            'heading' => '注文 #:order_idの領収書 （#:invoice_id）',
            'subject' => '注文 #:order_idの領収書',
            'summary' => '注文内容',
        ],
        'shipment' => [
            'heading' => 'El Envío #:shipment_id  ha sido generado por el pedido #:order_id',
            'inventory-heading' => 'Nuevo envío #:shipment_id ha sido generado por el pedido #:order_id',
            'subject' => 'Envío de tu pedido #:order_id',
            'inventory-subject' => 'Nuevo envío ha sido generado por el pedido #:order_id',
            'summary' => '注文内容',
            'carrier' => '配送業者',
            'tracking-number' => '追跡番号',
            'greeting' => 'El pedido :order_id ha sido enviado a :created_at',
        ],
        'forget-password' => [
            'subject' => 'パスワードをリセットする',
            'dear' => '様 :name',
            'info' => 'お客様のパスワードリセットのリクエストがあったため、ご連絡致します。',
            'reset-password' => 'パスワードのリセット',
            'final-summary' => 'Si no has solicitado cambiar de contraseña, ninguna acción es requerida por tu parte.',
            'thanks' => 'ありがとうございます。'
        ],
        'customer' => [
            'new' => [
                'dear' => '様 :customer_name',
                'username-email' => 'Nombre de usuario/Email',
                'subject' => 'Nuevo registro de cliente',
                'password' => 'パスワード',
                'summary' => 'Tu cuenta ha sido creada en Bassar.
                Los detalles de tu cuenta puedes verlos abajo: ',
                'thanks' => 'ありがとうございます。',
            ],

            'registration' => [
                'subject' => 'Nuevo registro de cliente',
                'customer-registration' => 'Cliente registrado exitosamente',
                'dear' => '様 :customer_name',
                'greeting' => '¡Bienvenido y gracias por registrarte en Bassar!',
                'summary' => 'Your account has now been created successfully and you can login using your email address and password credentials. Upon logging in, you will be able to access other services including reviewing past orders, wishlists and editing your account information.',
                'thanks' => 'ありがとうございます。',
            ],

            'verification' => [
                'heading' => 'Bassar - Verificación por correo',
                'subject' => 'メールでの確認',
                'verify' => 'アカウント確認',
                'summary' => 'このメールは、ご本人様確認のためにお送りしています。下記のリンクからアカウント確認をお願いします。'
            ],

            'subscription' => [
                'subject' => 'Subscripción mail',
                'greeting' => ' Bienvenido a Bassar - Subscripción por mail',
                'unsubscribe' => 'Darse de baja',
                'summary' => 'Gracias por ponernos en tu bandeja de entrada. Ha pasado un tiempo desde que leyó el último correo electrónico de Bassar, y no queremos abrumar su bandeja de entrada. Si ya no quiere recibir
                las últimas noticias de marketing, haga clic en el botón de abajo.'
            ]
        ]
    ],

    'webkul' => [
        'copy-right' => '© Copyright :year Webkul Software, All rights reserved',
    ],

    'response' => [
        'create-success' => ':name created successfully.',
        'update-success' => ':name updated successfully.',
        'delete-success' => ':name deleted successfully.',
        'submit-success' => ':name submitted successfully.'
    ],
];