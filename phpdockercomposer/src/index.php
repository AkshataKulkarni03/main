<?php

http_response_code(404);
echo json_encode(array('message' => "Not Found"));