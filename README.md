# **初探 Laravel Broadcasting (廣播)**
Laravel Documents : [*Broadcasting - Laravel*](https://laravel.com/docs/broadcasting)

## # 前言
首次嘗試在 Laravel 上實現 Real-Time 的 Broadcast，照著教學影片和 Laravel 官方提供的 Documents，做出個簡單的範例 ~~(想不到頗簡單的)~~。因此做了簡單的筆記介紹如何在 Laravel 上進行推播，待日後有更深入的研究。

* [影片網址](https://youtu.be/kNAZNQnig3E)

## # 簡介
隨著 Real-Time Messaging ([RTMP](https://en.wikipedia.org/wiki/Real-Time_Messaging_Protocol)) 技術的進步，Web 上的運用也漸漸普及。Laravel 對此更是提供了支援 Broadcast 的方法，使用的是 [pusher](https://pusher.com/) 這個 Library，遵守 [WebSocket](https://en.wikipedia.org/wiki/WebSocket) 的協定，配合 Laravel Echo 去實現 Broadcast。

總而言之就是藉由偵測 (Polling) 事件 (Events) 的發生 (Occur)，達到收發訊息的功能。

![PUSHER](https://www.leggetter.co.uk/images/pusher.png)

## # 環境設置

### 新建一個 Laravel 的專案

* 執行下列 Command

```shell=
> composer create-project laravel/laravel [name] [version]
```

### 到 Pusher 的官方網站註冊 & 申請 App

* 註冊帳號

![](https://i.imgur.com/hvJdYXZ.png)

* 建立 App

這裡的 Name 可以隨機命名， Cluster 則關係到之後的環境設定 (.env)

![](https://i.imgur.com/VZVm5jd.png)

### 參數設定

完成 Pusher 的註冊及申請 App 後，會得到一些~~看起來像亂碼的~~環境參數。

在 Getting Started 畫面右側
```ruby=
app_id: '446355',
key: 'da90061c959a4a64b8d1',
secret: '9847d01f6a3c5bcd5bc4',
cluster: 'ap1',
encrypted: true
```

* 將這之中的 `app_id`、`key`、`secret` 放入環境設定 `.env` 中

- [x] `/.env`

![](https://i.imgur.com/3l56ogt.png)

* 將 Broadcast 的 Driver 改成 Pusher

![](https://i.imgur.com/fyvwwEb.png)

#### 議題討論：
> `event(new eventTrigger());` 是用來觸發 Event 的，也可以改用 `broadcast(new eventTrigger());` 來 Trigger (broadcast 繼承於 event)。
> 

### 安裝 Pusher

```shell=
> composer require pusher/pusher-php-server [version]
```

### `broadcast.php` 的組態設定 (Configuration)

- [x] `/config/broadcast.php`

* 將 Default 的 Driver 改成 Pusher

![](https://i.imgur.com/P7RSIBT.png)

這裡是為了啟動 Pusher 的服務，這個設定會使 Pusher 這個服務去 `.env` 找 App 的環境參數。

影片中是改寫成 `'default' => 'pusher'`，但因為 `BROADCAST_DRIVER` 在 `.env` 中已經改好 Driver 了，所以我只將後面的 Default Value 改成 Pusher。

* 輸入 Pusher 支援的 Options

![](https://i.imgur.com/G99vSGu.png)

在 Pusher 的環境裡頭有 `cluster: 'ap1'` 與 `encrypted: true` 這兩個參數，需要加入在 `'options'` 裡面才能讓 Pusher 支援。

#### 議題討論：
>cluser：[Cluster Configuration](https://pusher.com/docs/clusters)。
>encrypted：定義是否為加密的 Connection。
>

### 安裝 Laravel Echo

Laravel Echo 是一個 JS 的 Library，可用來追蹤 (或稱訂閱) Channel 發出的 Broadcast。我們將用 [NPM](https://www.npmjs.com/) 這個套件管理員 (Package Manager) 去安裝 Laravel Echo 以及 `pusher-js`。

([DIY 下載及安裝 NPM 點此](https://www.npmjs.com/get-npm?utm_source=house&utm_medium=homepage&utm_campaign=free%20orgs&utm_term=Install%20npm))

1. 首先安裝 npm

```shell=
> npm install
```

2. 安裝 Laravel Echo 與 `pusher-js`

```shell=
> npm install --save laravel-echo pusher-js
```

## # 實作推播事件 (Event of Broadcast)

環境建置好之後，我們得做一個 Event 去觸發 Broadcast，也就是實作一個廣播系統，這裡算是 Server-Side 的部分。

### 製作出一個 Event 和綁定 Event 的 Listener。

```shell=
> php artisan make:event [name]
> php artisan make:listener [name] --event [event-name]
```

在廣播 (Broadcast) 時讓監聽 (Listen) 到指定頻道 (Channel) 的接收方 (Receiver) 接收到事件發生。

> 想像一下教室裝的喇叭 (Receiver)，裏頭有接到學務處的線 (Listener)，當學務處廣播 (Broadcast) 時，可以選擇年級 (Channel) 來進行廣播。
> 

~~(自己都有點搞混)~~

### 讓 Event 實行 Broadcast

- [x] `/app/Events/eventTrigger.php`

在 Event 的後面加上 `implements ShouldBroadcast`

![](https://i.imgur.com/ZWYK5Z3.png)

`ShouldBroadcast` 是用來實行 (Implement) 廣播到 Channels 的類別，這裡因為 Event 運用到的是 `ShouldBroadcast` 的功能，所以由 Event 實行。

### 實作 Broadcast 的 Channel

- [x] `/app/Events/eventTrigger.php`

有了 Event 還不夠，我們得製作一個 Channel 讓 Receiver 可以透過 Listen 到這個 Channel 的 Broadcast 運行 Event。

你能想像假如你有 100 個數位電視的頻道，結果打開來都是同一個節目嗎？這就是 Channel 的功用！~~我盡力解釋了😭。~~

![](https://i.imgur.com/LLQX8Yk.png)

這裡要把 Private 拿掉，使其他類別可以存取這個頻道。

## # Broadcasts 的接收

### 開啟 `bootstrap.js` 將啟動 Laravel Echo 元件的程式碼取消註解

- [x] `resources/assets/js/bootstrap.js`

![](https://i.imgur.com/WSTkVfl.png)

主要是設定 `bootstrap.js` 開啟 Pusher 的 Service，並且定義 Pusher 是用來實現 Broadcast 的第三方支援 (Plugin)。

### 設定 Broadcast 的 Driver 和 Key

- [x] `resources/assets/js/bootstrap.js`

![](https://i.imgur.com/eKie5jd.png)

在這裡也要跟 Receiver 告知使用的 Cluster 以及 Encrypt 與否。

***總之就是所有東西都打上去，不然 Receiver 端收不到 Listen 的 Channel 發出的 Broadcast 😅。***

### 隨時編譯 JS 檔案

```shell=
> npm run watch
```

我們在安裝好 NPM 後，NPM 會追蹤所屬的資料夾 (Laravel 預設會指向 `resources/assets/js`)。執行這個指令會切入到 Watch Mode 追蹤檔案的變動，並自動編譯。

## # 實作 Receiver

### 接收 Broadcast 的 view

- [x] `/resources/views/eventListener.blade.php`

簡單做一個頁面呈現 Event 的效果，這裡是用 Client-Side 作為 Receiver 的部分。

![](https://i.imgur.com/QjnqTU0.png)

這裡包含檔名都是按照影片進行，比較好結合影片教學。

#### 議題討論：

> 1. CSRF(Cross-site request forgery) token
> 因為 Laravel Echo 需要存取 Session 的 CSRF token，必須要在 `<head>` 加上後設資料 `<meta name="csrf-token" content="{{ csrf_token() }}">` 來取得。
> 
> 2. `id="app"` 容器
> 需要有個容器接 Trigger 的 Event，可自由命名，之後 [Vue](https://vuejs.org) 需要抓到相同的 Container。
> 
> 3. `charset="utf-8"` 設定編碼
> 通常 Import 外來的 JS 檔案不需要識別編碼。但在這因為 Broadcast 的 Message 有 Key 及其它的參數設定，編碼必須明確告知。
> 

~~反正就是照著打就對了。~~

### Vue 綁定 Container

- [x] `/resources/assets/js/app.js`

![](https://i.imgur.com/TOz3uGL.png)

然後你會發現 `app.js` 已經貼心的幫我們寫好綁定一個 `id` 是 `app` 的容器了。

### 元件產生後 (Created) 去 Listen 到特定 Channel

- [x] `/resources/assets/js/app.js`

![](https://i.imgur.com/KjzM8AE.png)

這裡實作當 Broadcast 發生後，Function 產生一個對話方塊。使用 Laravel Echo 去監聽 Channel，Channel 的名稱與 Event 製作的相同，Listen 到的 Event 則是我們先前製作出的 `eventTrigger`。

相關語法可以看看 Vue 的[官方文件](https://vuejs.org/v2/guide/)

```javascript=
.listen('eventTrigger', (e) => {});
```

這邊的 `(e) => {}` 是 ES6 的 Arrow Function，可以參考 [http://es6-features.org/](http://es6-features.org/)。

## # Route 設定

現在我們已經實現簡易的廣播功能了！最後要加上發送方 (Sender) 與接收方 (Receiver) 的 Route 規則來測試功能。

### 直接在 `web.php` 加上這兩個路由規則

- [x] `/routes/web.php`

```php=
/* receiver */
Route::get('/alertBox', function () {
    return view('eventListener');
});

/* sender */
Route::get('/fireEvent', function () {
    event(new eventTrigger());
});
```

因為在使用了 `eventTrigger` 這個類別，所以在 `web.php` 裡需要 Use 其他模組。

```php=
use App\Events\eventTrigger;
```

## # 測試 & 完成

終於到了這一步，接下來我們要測試

1. Sender 成功送出 Broadcast
2. Receiver 成功接收 Broadcast

### Sender 端

1. 首先開啟 Pusher 裡頭的 Debug Console，去追蹤我們的 Pusher 是否有確實 Work。

![](https://i.imgur.com/XVGonXs.png)

2. 開啟在 `web.php` 內 `/fireEvent` 對應的網址

這個 Route 會製作一個 Broadcast 的 Event。例：`http://127.0.0.1/pusher/public/fireEvent`

3. 查看 Pusher 的 App 是否有接到 Sender 的 Broadcast

沒意外的話 Debug Console 會是這種情況。

**(Debug Console 左邊)**
![](https://i.imgur.com/avQv7rp.png)

**(Debug Console 右邊)**
![](https://i.imgur.com/eKGpn45.png)

裡面記錄了包含觸發的 Event 跟 Broadcast 到的 Channel。

不論內容是否相同，如果有通知倒進來都表示 Pusher 有確實運作了！

### Receiver 端

1. 開啟 `web.php` 內 `/alertEvent` 對應的網址

這個 Route 會回傳一個 Receiver 的 View。

2. 重新發出一個 Broadcast

就是上一個開啟的頁面

3. 確認 `/alertEvent` 這個頁面是否有收到 Channel 發出的 Broadcast

![](https://i.imgur.com/JC6VLSz.png)

頁面有確實執行我們在 `app.js` 實作的 Function。

## # 完成

這樣就是一個簡單的 Laravel Broadcasting 應用了！如果要更深入的探討可以去 Laravel 的官方網站學習更多有關 Broadcasting 的實作，還有 Pusher 官方網站提供的 Documentations 與 API，祝你有個愉快的開發經驗！
