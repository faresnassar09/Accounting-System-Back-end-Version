<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/external/transaction/create', 'POST');
$request->headers->set('Host', 'e-wallet.localhost');
$request->headers->set('Accept', 'application/json');
$request->headers->set('Authorization', 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIwMWEwNDBkOC00ZTJhLTcwMTMtjZmNi1iYTQ5ZWFlMWExOGUiLCJqdGkiOiI2NWI1ODg3NGE4ODI2MGFkZTg5ZDQ3NGE1YjdhYTVhZmFlN2M5ZDVhZjg3OTU3YzcxYzE2ODcxNDEwNTI3Zjk4YTRkYWIwZDVjZjI0ZDU5ZSIsImlhdCI6MTc5MTQyNjk0MS41MzE0OTcsIm5iZiI6MTc5MTQyNjk0MS41MzE1LCJleHAiOjE4MjI5NjI5NDEuNTI0OTI4LCJzdWIiOiIwMWEwNDBkOC00ZTJhLTcwMTMtjZmNi1iYTQ5ZWFlMWExOGUiLCJzY29wZXMiOltdfQ.pp4Tp-pPwdHN-DMxtYJLEMK5PlukuYO92EHiT9m2g84wGtQkGK8y7EZzTeXvcXbKVF6uRR1q3E-fu0eriBG5qVGalRm3Qiw_rN_GjFR97pn2mU1bWKuYvwmBk1Wxhic5cVp0wP1NO5VvqAo5pUG63mhq8ZHs3cbwJl7mxWWbUbnhzBxEQqGIAH1yVP3L0Xllwj0_j2q1bG30Zfha02FYtUF43EbHGd9htd5nvrYRnNwKFOmqidec45v7Tz7Ya-6ljBzpO19dBvGLIyDK1a-9Uz57OafIcAcTHzrF5LGj05kzZgGlM2REMc1gqqquaQudkkBs6xmuRFf0vn0r5HAvB6-LpV5-1jX3zb47Brs6k8lJAfvcJt67uwxDXkuBrrrAqEsNBZCkSZ0MjhdHV7d5DhjGZF2VI9y23RlPCEvWQf451XMN4lpGu0Q2UZTu_oauARyoCaTZGoLznV5DFFygr4d1EGS9fFFS9l_k8259uQgh6MyySOgFKWpawVWPQ2qqTLx4ZHyWxdGTxReKYM7dhd_4W_73SXDyd4Ng2esiOFFs52f1o4vybfKy9M2PsFha4ZKPL_aSp5_qaOYcVW8uiF30PkxpIhS2Ck36MeWBnDH_d66_6cZyu5FycOsVsaFynrQ8J7luVIe69TfVwVlZ_AqbYwPMC5Z9c9s-ufNUdKQ');

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
$content = $response->getContent();
if ($response->getStatusCode() == 500) {
   $data = json_decode($content, true);
   echo "Message: " . ($data['message'] ?? $content) . "\n";
} else {
   echo "Content: " . $content . "\n";
}
