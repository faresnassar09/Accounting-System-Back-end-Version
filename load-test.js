import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '30s', target: 30 }, // تصاعد لـ 10 مستخدمين
        { duration: '1m', target: 60 }, // ثبات على 30 مستخدم
        { duration: '30s', target: 100},
        { duration: '20s', target: 0 },  // هبوط تدريجي للصفر
  // هبوط تدريجي للصفر
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // 95% من الطلبات أقل من 500ms
        http_req_failed: ['rate<0.01'],  // نسبة الأخطاء أقل من 1%
    },
};

export default function () {
    // 1. رابط الـ API الخاص بالتحويل في مشروعك (تأكد لو محتاج تحط اسم السابدومين الخاص بالتننانع هنا)
    const url = 'http://e-wallet.localhost/api/external/transaction/create';
    // 2. الـ JSON Payload للتحويل بين المحافظ
    const payload = JSON.stringify({
        "timestamp": "2026-03-25 14:30:00",
        "description": "Transfer from multiple wallets to Wallet B",
        "total_amount": 1500.00,
        "parties": {
            "senders": [
                {
                    "source_reference": "w-550-x",
                    "amount": 500.00
                },
                {
                    "source_reference": "w-200-x",
                    "amount": 1000.00
                }
            ],
            "receivers": [
                {
                    "source_reference": "w-990-z",
                    "amount": 1500.00
                }
            ]
        }
    });

    // 3. الـ Client Token الجديد
    const token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIwMWEwNDBkOC00ZTJhLTcwMTMtYjZmNi1iYTQ5ZWFlMWExOGUiLCJqdGkiOiIyZjNkMzljMzQ4ZTczYzNkNzg5MjdlNzc3OGJlMGY0Zjk3NmNhMGY0MzQwMTJmODMxZjRjYmY4Mjk2YTFjYjkwZTJkMzhkZGRjNDgwOTFkYiIsImlhdCI6MTc5MTQyODcyNS44NDc4NDgsIm5iZiI6MTc5MTQyODcyNS44NDc4NTMsImV4cCI6MTgyMjk2NDcyNS44NDAyNTEsInN1YiI6IjAxYTA0MGQ4LTRlMmEtNzAxMy1iNmY2LWJhNDllYWUxYTE4ZSIsInNjb3BlcyI6W119.ScqwgKiehrc3Q6Pwux6PJc4OoVqqTjWHUuBQ4DyyZiZyFCqRCJdrbgFMrAF9UPvCfDfO-A1qZHsq0_bo7di7x-7dqCXzXBpy8UB5iTS6Vf4NukeNa42xeMdbPFvaaQEas0Gmwyfwq-ZJCAj6LMLlIBQYIctbKG4jJAc5CXwkExsozZaiXffzMC8oy2madZhsIcZV_YuIPUMPrOkfn1pUgyiGo95WDXlIqZZ02F49tQrXEXs-Z_dJCcpTtgwu05gjzSpoCCqhG13fbLaCQEmYqWhYEfH5C4YS25DZch114aE6ZWUkp14JFORsAvXn_SW41GdACR1rYMfl7Jjexh_VkRnN4Q0El5i9SZ9tFk-2K7hiMUKX7ZUdskbKaQGyIsHo66HcUSJ_GRwe5Z6gGLVIQnsyi-q1RGDLzvuTOC3fwECoAdbXH703-zUl9pzTJ-ug67rAGj79mkXWUhJAChzTTAdZUWUx5dOlOVpt7C30ZPu8EvcuvCB5KFzNYDXGe7QQY_tFH5xlf4c-sQC-B9mF3PKN6xM6YA7h6BfhWTrdaTy4e7q0QHHPnOmnPohaQytQgOv_iU3eZxK4WCTvpbvAwvw9bPYQN7rcBS6q_sUOA82u0Nk4k8GoI4a4NnLkacE1Xc_oUAgQjJrvE_DBffOxgobUpXLojRbm4YquMxiqc6Y";
    
    // 4. إعداد الـ Headers (ولو مشروعك بيعتمد على هيدر للـ Tenant مثل X-Tenant، أضفه هنا)
    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
            // 'X-Tenant': 'tenant_id', // لو بتحتاج تبعت Tenant معرف في الهيدر
        },
    };

    const res = http.post(url, payload, params);

    // للتأكد أثناء الاختبار لو فيه أي رد غير متوقع
    if (res.status !== 200 && res.status !== 201) {
        console.log(`Status: ${res.status} | Body: ${res.body}`);
    }

    check(res, {
        'status is 200 or 201': (r) => r.status === 200 || r.status === 201,
        'response time < 500ms': (r) => r.timings.duration < 500,
    });

    sleep(1);
}
