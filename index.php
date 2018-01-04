<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<!--This page was created by Jennifer Argote for IT207 002.-->

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>Lab 10 || Grocery Store</title>
	<link rel="stylesheet" type="text/css" href="lab10styles.css" />
</head>

<body>
	<div id="container">
		<h1>Grocery Store Items</h1>
		<?php
			include("../private/configGS.php");
			$tableName = "catalog";
			$filterTableName = "categories";
			$catFilter = $_GET['catFilter'];
			
			$connection = mysqli_connect(DBHOST, DBUSER, DBPWD, DBNAME);
			//checks to see if there was a problem with connection to database
			if (mysqli_connect_errno()) {
				die("<h3>CONNECTION ERROR (". mysqli_connect_errno() .")</h3><p>Sorry, there was a problem: ". mysqli_connect_error($connection). "</p>");
			}
			else {
				//retrieves $_GET value from url, if available
				if (!empty($_GET["cur"])) {
						$cur = $_GET["cur"];
				}
				else {
					$cur = 0;
				}
				
				//filters query if filter was enabled
				if (isset($catFilter) && is_numeric($catFilter)) {
					$sqlString = "SELECT * FROM $tableName WHERE intCatID=$catFilter LIMIT $cur, 10";
					$queryResult = @mysqli_query($connection, $sqlString);
				}
				else {
					$sqlString = "SELECT * FROM $tableName LIMIT $cur, 10";
					$queryResult = @mysqli_query($connection, $sqlString);
				}

				$sqlFilterString = "SELECT * FROM $filterTableName";
				$queryFilterResult = @mysqli_query($connection, $sqlFilterString);
				
				//checks if able to execute query
				if ($queryResult === false || $queryFilterResult === false) {
					echo "<p>Unable to execute the query.  Error code: ". mysqli_connect_error($connection). "</p>";
				}
				else {
					//dropdown menu for category filter
					$filterRows = mysqli_num_rows($queryFilterResult);
		?>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
			<select name="catFilter">
				<option value="all">Show All</option>
			<?php
					while (!is_null(($row = mysqli_fetch_assoc($queryFilterResult)))) {
						echo "<option value=\"{$row['intCatID']}\"";
						if (isset($catFilter) && $catFilter == $row['intCatID']) {
							echo " selected";
							$catFilterVal = $row['vcharName'];
						}
						echo ">{$row['vcharName']}</option>";
					}
			?>
			</select>
			<input type="submit" value="Filter" />
		</form>
			<?php
						//displays retrieved records
						$num_rows = mysqli_num_rows($queryResult);

						if ($num_rows == 0) {
							echo "<p>Sorry, there currently aren't any items for " . $catFilterVal . " in stock.</p>";
						}
						else {
			?>
		<table>
			<tr>
				<th>Item</th>
				<th>Price</th>
			</tr>

							<?php
							while (!is_null(($row = mysqli_fetch_assoc($queryResult)))) {
								echo "<tr>";
								echo "<td class=\"productName\">{$row['vcharName']}</td>";
								echo "<td class=\"price\">\${$row['fltPrice']}</td>";
								echo "</tr>";
							} //end while loop
						} //end else for query filter return in stock
					} //end else for query successfully executed
						
			?>
		</table>
		
		<div id="nav">
			<p>
			<?php
					if (isset($catFilter) && is_numeric($catFilter)) {
						$sqlString = "SELECT * FROM $tableName WHERE intCatID=$catFilter";
					}
					else {
						$sqlString = "SELECT * FROM $tableName";
					}
					$queryTotalResult = @mysqli_query($connection, $sqlString);
					
					//checks if able to execute query
					if ($queryResult === false) {
						echo "<p>Unable to execute the query.  Error code: ". mysqli_connect_error($connection). "</p>";
					}
					else {
						$num_rows = mysqli_num_rows($queryTotalResult);
						$cur = 0;
						$tenMore = true;
						$count = 1;
						$pages = $num_rows / 10;
						
						//determinds if there should be one more page with a group of records that are less than 10 for the links portion
						if ($pages % 10 != 0) {
							$pages++;
						}
						
						settype($pages, "integer");
						
						//display links to other pages, if applicable
						while ($tenMore == true || $pages > 0) {
							//sets begining of URL with category filter, if applicable
							if (isset($catFilter) && is_numeric($catFilter)) {
								$begUrlStr = "<a href=\"index.php?cur=". $cur. "&catFilter=". $catFilter. "\">";
							}
							else {
								$begUrlStr = "<a href=\"index.php?cur=". $cur. "\">";
							}

							//displays link when there are a group of records that are divisible by 10
							if ($pages > 1) {
								echo $begUrlStr. ($count). " - ". ($count+9). "</a> | ";
								$count += 10;
								$cur +=10;
							}
							else { 
								//displays last/only link if there is only one record
								if ($num_rows == $count) {
									echo $begUrlStr. ($count). "</a>";
								}
								else {
									//displays last/only link if the last group of records is less than 10 but more than only 1 record
									if ($num_rows > $count) {
										echo $begUrlStr. ($count). " - ". ($num_rows). "</a>";
									}
									//nothing is displayed if total records in query are divisible by 10
								}
								$tenMore = false;
							}
							$pages--;
						} //end while
					} //end else query able to be executed
				} //end else could connect to mysql
				mysqli_close($connection);
			?>
			</p>
		</div>
	</div>
</body>
</html>