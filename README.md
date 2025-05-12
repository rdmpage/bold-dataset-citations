# BOLD dataset citations

Gathering links between DNA barcode datasets in BOLD and publications citing those datasets.

Note that in an ideal world we could use MakeDataCount’s Data Citation Corpus, but it’s a mess. In version 2 of that corpus BOLD datasets are recorded as citing themselves(!).

## Reading

Zeng, Tong, Longfeng Wu, Sarah Bratt, and Daniel E. Acuna. ‘Assigning Credit to Scientific Datasets Using Article Citation Networks’. Journal of Informetrics 14, no. 2 (1 May 2020): 101013. https://doi.org/10.1016/j.joi.2020.101013.

## BOLD datasets

BOLD datasets have DataCite DOIs of the form `10.5883/DS-*`. Given a list of these, I retrieved metadata from DataCite using their API (`harvest-dois.php`), parsed the resulkting JSON (`parse-dois.php`) and stored the results in the `dataset` table.

I used Google Scholar to try and link these datasets to publications. Two searches were performed, one for articles mentioning the `DS-xxxx` identifier (`harvest-gs.php`), the other for matches on the dataset title (which was retrieved from DataCite) (`harvest-gs-title.php`). The results of these two searches were parsed using `parse-gs.php` and `parse-gs-title.php` and stored in a SQL database in the table `citation`.

In the `citation` table the column `match` is “1” if the identifier string is found in the Google Scholar results, otherwise it is NULL. This is useful to filter potentially spurious results, but is also vulnerable to false positives, for example, if the dataset identifier resembles another term in the text, or an author name.

To help filter matches based on dataset name I computed the Levenshtein distance between the dataset title and the article title(s) returned by Google Scholar using `match.php` and stored this in the `score` field in the `citation` table. This test is also error-prone. It can miss titles that are clearly related but differ in word order, and it will fail if the language of the article is different from that of the dataset. Because the datasets themselves have been indexed by some of Google Scholar’s sources, it is also possible that the Google Scholar search result is the dataset itself.

Once the two searches were complete the results were examined using the two criteria above (presence of dataset identifier, match to dataset title). Once clear examples of matches were identified using these methods, the remaining results were manually inspected. The column `accepted` records a match that is deemed to be correct. The data to be manually checked was exported using `export.php` and loaded into a Google Sheet.

After the Google sheet was edited, it was added to the SQL dataset as the table `cleaned`. This table was run through further automated checking using `check.php`. URLs that hadn’t been accepted or rejected were resolved, and the name of the dataset was looked for in either the HTML or PDF for the article.

The table `cleaned` was then manually checked one more time, and declared to be “complete”, knowing that there are obviously still gaps. The script `summary.php` is used to summarise and explore the results.

Note that `cleaned` is a subset of `dataset` as many datasets returned no result when searched for on Google Scholar.

The table `publications` contains URLs from the Google Scholar results, and metadata extracted by resolving the URL using `url2doi.php` and looking at `<meta>` tags. An attempt was also made to extract DOIs from URLs using regular expressions in `url2extract.php`.

### Output

The table `dataset` represents the data for BOLD datasets. The table `cleaned` represents a mapping between those datasets and papers that publish and/or cite those datasets.

## Examples

### Searches that return dataset instead of citing work

DS-KINA https://cir.nii.ac.jp/crid/1880865118193612416

### Searches returning theses and preprints

Some searches return thesis and preprints.

https://edoc.ub.uni-muenchen.de/27000/ has DOI for thesis in body but not header

https://oulurepo.oulu.fi/handle/10024/43702 has DOI of publication in DC.relation

### Related identifiers

10.5061/dryad.rjdfn2z9b and 10.5883/DS-CHIRI are related (Dryad and BOLD). This information is in the metadata for the Dryad DOI.

### Title mismatch

https://bold-view-bf2dfe9b0db3.herokuapp.com/?recordset=DS-ABSKMA matches https://doi.org/10.3161/150811013X678937

### Cites lots of BOLD datasets

https://doi.org/10.1371/journal.pone.0116612

## Manual

Some examples of manually matched records:

| Dataset | Citation | Comment |
|--|--|--|
| DS-ANIC1A | 10.1007/978-3-031-32103-0_1 | citation |
| DS-ANIC1B | 10.1007/978-3-031-32103-0_1 | citation |
| DS-ASNBPAR | 10.1111/afe.12508 | |
| DS-ASNBPAR | 10.32942/osf.io/mdua8 | preprint |
| 10.5883/DS-CNEPETA |10.57065/shilap.651 |  |
| 10.5883/DS-DRYLOJA | 10.7818/ECOS.2016.25-2.09 |  |
| 10.5883/DS-EBER | 10.1051/kmae/2020038 |  |
| 10.5883/DS-MERGALBE | http://www.nw-ornithologen.de/images/textfiles/charadrius/charadrius51_57_62_Klein_etal_ErstbrutnachweisZwergsaegerDeutschland.pdf | |






