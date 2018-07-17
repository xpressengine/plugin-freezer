<?php
return [
    'timer' => 365, // 휴면처리를 할 마지막 로그인 시도로부터의 경과일
    'notify_timer' => 334, // 휴면처리 예고 알림을 발송할 마지막 로그인 시도로부터의 경과일
    'freeze_type' => 'freeze', // "delete"|"freeze" 휴면처리 방식, 삭제 or 분리
    'scheduled_at' => [
        'notify'=>'6:00', // 휴면처리 배치 작업 시작시점 (6:00 => 매일 6시)
        'freeze'=>'6:30', // 휴면처리 배치 작업 시작시점 (6:00 => 매일 6시)
    ],
    'queue_size' => 1,
    'queue' => [
        'notify' => 'sync',
        'freeze' => 'sync'
    ],
    'email' => [
        'notify' => ['subject' => '휴면게정 처리 예정 안내', 'content' => function($user, $type, $config) {

            if($config['freeze_type'] === 'freeze') {
                return "{$user->getDisplayName()}님, {$user->created_at->format('Y년 m월 d일')}에 가입한 계정이 최근 11개월간 이용되지 않아 1개월 후 휴면 상태로 전환될 예정입니다. <br> 휴면상태로 전환된 후에는 개인정보를 분리하여 보관하게 됩니다. 차후 휴면 상태로 전환된 계정으로 다시 서비스를 이용하기 위해서는 사이트의 <a href=\"{{route('login')}}\">로그인</a> 페이지에서 다시 로그인을 하시면 됩니다. 감사합니다 ";
            } else {
                return "{$user->getDisplayName()}님, {$user->created_at->format('Y년 m월 d일')}에 가입한 계정이 최근 11개월간 이용되지 않아 1개월 후 탈퇴 처리될 예정입니다. <br> 탈퇴 처리된 후에는 다시 서비스를 이용하기 위해 사이트의 <a href=\"{{route('auth.register')}}\">회원가입</a> 페이지에서 새로운 계정으로 가입하여야 합니다. 감사합니다 ";
            }
        }],
        'freeze' => ['subject' => '휴면계정 처리 결과 안내', 'content' => function($user, $type, $config) {
            return "{$user->getDisplayName()}님, {$user->created_at->format('Y년 m월 d일')}에 가입한 계정이 최근 1년간 이용되지 않아 휴면 상태로 전환되었습니다. <br> 사이트의 <a href=\"{{route('login')}}\">로그인</a> 페이지에서 다시 로그인하실 경우 계정이 복구되며, 서비스를 정상적으로 다시 이용할 수 있습니다. 감사합니다 ";
        }],
        'delete' => ['subject' => '휴면계정 삭제처리 안내', 'content' => function($user, $type, $config) {
            return "{$user->getDisplayName()}님, {$user->created_at->format('Y년 m월 d일')}에 가입한 계정이 최근 1년간 이용되지 않아 탈퇴 처리되었습니다. <br> 사이트의 <a href=\"{{route('auth.register')}}\">회원가입</a> 페이지에서 새로운 계정으로 가입후 서비스를 다시 이용할 수 있습니다. 감사합니다 ";
        }],
        'unfreeze' => ['subject' => '휴면개정 복구 결과 안내', 'content' => function($user, $type, $config) {
            return "{$user->getDisplayName()}님, 장기간 서비스를 이용하지 않아 분리 보관했던 계정 정보가 다시 정상적으로 복구되었습니다. <br> 이제부터 서비스를 다시 이용할 수 있습니다 ";
        }],
    ],
    'use_unfreeze_page' => false,   // 비활성계정 활성화 변경 안내 페이지 사용 설정
    'unfreeze_skin_id' => 'unfreeze/freezer/skin/freezer@default', // 스킨 컴포넌트 아이디,
    // 비밀번호 변경
    'password_protector' => [
        'use' => false,
        'timer' => 180,  // 비밀번호 변경 페이지로 이동 할 마지막 로그인 시도로부터의 경과일 (30을 할 경우 1개월로 설정, 안내) (180 => 6개월, 190 => 190일 로 표기)
        'skin_id' => 'password_protector/freezer/skin/freezer@default', // 스킨 컴포넌트 아이디,
        'send_skip_email' => false,  // 다음에 변경하기 선택 시 안내이메일 발송 여부
        'next_check_timer' => 30,   // 다음에 변경하기 할 경우 기간 설정 (30을 할 경우 1개월로 설정, 안내) (30 => 1개월, 40 => 40일 로 표기)
    ],
];
