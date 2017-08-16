# plugin-freezer

이 어플리케이션은 Xpressengine3(이하 XE3)의 플러그인입니다.

이 플러그인은 ['정보통신망 이용촉진 및 정보보호 등에 관한 법률 제 29조'](http://www.law.go.kr/법령/정보통신망이용촉진및정보보호등에관한법률/(20170726,14839,20170726)/제29조) 및 ['동법 시행령 제 16조'](http://www.law.go.kr/법령/정보통신망이용촉진및정보보호등에관한법률시행령/(20170726,28210,20170726)/제16조)에 의거
장기간동안 로그인을 하지 않은 사용자 계정을 회원 정보와 별도 분리하는 기능을 제공합니다.

> 이 플러그인을 사용하기 위해서는 먼저 [이메일 전송 설정](https://laravel.kr/docs/5.1/mail)이 되어있어야 합니다.

## Features

- 휴면 계정 처리 예고: 장시간동안 로그인하지 않아 휴면처리 될 예정인 계정의 이메일으로 '휴면계정 처리 알림'을 보냅니다.
- 휴면계정 처리: 휴면처리 대상 계정을 휴면처리 합니다. 휴면처리된 계정의 정보는 별도 테이블에 저장되거나 영구삭제 됩니다.
	- 휴면처리시 해당 계정의 이메일로 휴면처리 결과가 통보됩니다.
	- 휴면처리된 계정으로 로그인이 시도될 경우, 휴면처리됐던 계정이 다시 복구됩니다.(단, 영구삭제된 계정은 복구 불가) 
- 휴면계정을 수동으로 복구할 수 있습니다.

## Installation

이 플러그인을 사용하려면 먼저 XE3가 설치돼 있어야 하며, 플러그인을 XE3에 설치를 해야 합니다.

### XE3 자료실을 사용하여 설치

XE3 자료실을 통해 이 플러그인을 설치할 수 있습니다. 자세한 설치 방법은 자료실에서 볼 수 있습니다.

### git clone을 사용하여 설치

1. 설치된 XE3의 `/plugins` 디렉토리에서 아래의 명령을 실행합니다.
	```
	$ git clone https://github.com/xpressengine/plugin-freezer.git ./freezer
	```
2. 설치된 디렉토리로 이동한 다음, `composer dump`를 실행합니다.
	```
	$ cd ./freezer
	$ composer dump
	```
3. `사이트관리페이지 > 플러그인 > 플러그인 목록` 페이지에서 '휴면계정 관리' 플러그인을 활성화합니다.

### Configuration

플러그인 실행시 적용되는 기본 설정은 `/plugins/freezer/config.php`에 저장되어 있습니다. 만약 기본 설정을 변경해서 사용하고 싶은 경우, `/config/production/services.php`에 `freezer` 항목을 생성하고, 원하는 설정을 변경하면 됩니다. 

`/config/production/services.php`의 `freezer` 항목에 지정한 설정은 기본설정을 덮어 씌웁니다.

```
// config/production/services.php

<?php
return [
  'freezer' => [
    'timer' => 730, // 기본 타이머를 2년으로 변경
  ]
];
```

위 코드는 휴면계정 처리 기준이 되는 타이머를 1년(기본)에서 2년으로 변경하는 코드입니다. 타이머 이외에도 많은 설정이 존재하며, 위와 같은 방식으로 변경할 수 있습니다.



### Usage

이 플러그인은 콘솔 명령어를 통해 작동합니다. 아래의 명령을 주기적으로 실행하십시오. 수동으로 실행하거나 `crontab` 또는 [스케쥴러](https://laravel.com/docs/5.1/scheduling)를 사용하여 하루에 1회씩 실행되도록 하십시오.

#### 휴면계정 처리 예고하기

지정된 기간(기본 11개월)동안 로그인한 적이 없는 회원에게 이메일로 휴면계정 처리 예고 알림을 전송합니다.

`php artisan freezer:notify` 명령을 사용하십시오.

```
$ php artisan freezer:notify

 Emails will be sent to 3 users. Do you want to execute it? (yes/no) [no]:
 > yes

[2017.08.16 11:54:27] Emails were sent to 3 users for notify about freeze.
```

#### 휴면계정 처리하기

지정된 기간(기본 1년)동안 로그인한 적이 없는 회원을 휴면계정 처리합니다. 

`php artisan freezer:freeze` 명령을 사용하십시오.

```
$ php artisan freezer:freeze

 3 users will be frozen. Do you want to execute it? (yes/no) [no]:
 > yes
 
[2017.09.16 11:59:16] 3 users ware frozen.

```

#### 휴면계정 복구하기

휴면처리된 계정을 수동으로 다시 복구할 수 있습니다.

`php artisan freezer:unfreeze [USER_ID]`를 사용하십시오.

```
$ php artisan freezer:unfreeze aa972e8b-6a73-459a-af18-22e7991d43ad

 'khongchi' users will be unfreezed. Do you want to execute it? (yes/no) [no]:
 > yes

the user was unfreezed.
```

> 수동으로 복구하지 않더라도 휴면처리 된 사용자가 다시 로그인을 할 경우 자동으로 계정이 복구됩니다.






