<?php
global $product;
$post_thumbnail_id = $product->get_image_id();

require __DIR__ . '../src/vendor/autoload.php';
function getClient() {
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP Quickstart');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
    //$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $client->setAuthConfig('decoreine-php-access.json');

    $accessToken = 'ACCESS_TOKEN'; // Use your generated access token
    $client->setAccessToken($accessToken);

    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');
    return $client;
}
function get( $client, $spreadSheetId, $range ) {
    $service = new Google_Service_Sheets($client);
    $response = $service->spreadsheets_values->get($spreadSheetId, $range);
    return $response->getValues();
}
function update( $client, $spreadsheetId ,$range,$values) {
    $service = new Google_Service_Sheets( $client );
    $updateBody = new Google_Service_Sheets_ValueRange( [
        'range' => $range,
        'majorDimension' => 'ROWS',
        'values' => $values,
    ] );
    $result = $service->spreadsheets_values->update( $spreadsheetId, $range, $updateBody, [ 'valueInputOption' => 'raw' ] );
}
function index($client, $spreadsheetId, $uid) {
    $range = 'site!A2:A';
    $values = get($client,$spreadsheetId, $range);
    if (empty($values)) {
        return -2; // no data found
    } else {
        $i=1;
        foreach ($values as $row) {
            $i++;
            if($row[0] == $uid)
                return $i;
        }
        return -1; // no id found
    }
}
function appendFromPost($client, $spreadsheetId,$range) {
    $service = new Google_Service_Sheets($client);
    $row = 0;
    $values = [
        [uniqid(), date("Y/m/d H:i:s"), $_POST[ 'name' ], $_POST[ 'phone' ], $_POST[ 'address' ]]
    ];
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);
    $params = [
        'valueInputOption' => "RAW"
    ];
    $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
}
function clear($client, $spreadhheetId) {
    $service = new Google_Service_Sheets($client);
    $range = "'test'!A1:C";
    $requestBody = new Google_Service_Sheets_ClearValuesRequest();
    $response = $service->spreadsheets_values->clear($spreadhheetId, $range, $requestBody);
}
function create($client) {
    $service = new Google_Service_Sheets($client);

    $spreadsheet = new Google_Service_Sheets_Spreadsheet([
        'properties' => [
            'title' => 'test-sheet'
        ]
    ]);
    $spreadsheet = $service->spreadsheets->create($spreadsheet, [
        'fields' => 'spreadsheetId'
    ]);
    printf("Spreadsheet ID: %s\n", $spreadsheet->spreadsheetId);
}
?>

<?php
$client = getClient();
$spreadsheetId = '1vOxtzOU4aP6Ztcj1bzviN1ZkOBBxe602rJlXXg8ietQ';

/*
$range = 'site!C5:C5';

$values = [
	[
		'Firas Ykhlef'
	],
// Additional rows ...
];
update($client, $spreadsheetId , $range, $values) ;
*/
$range = 'site!A1:E';
appendFromPost($client, $spreadsheetId, $range);

$values = get($client,$spreadsheetId, $range);

if (empty($values)) {
  print "No data found.\n";
} else {
  echo "Name, Major:"."<br>";
  foreach ($values as $row) {
	  echo $row[0].$row[1].$row[2]."<br>";
  }
}

$i = index($client, $spreadsheetId, $uid = '626de4a26d762');
echo $i;
?>