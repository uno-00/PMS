<?php

// Railway / load-balancer probe — no Laravel bootstrap required.
http_response_code(200);
header('Content-Type: text/plain; charset=UTF-8');
echo 'ok';
