;****************************************************************************
; *   Copyright (C) 2007 by Jochen Kothe                                    *
; *   jkothe@proi-php.de                                                    *
; *                                                                         *
; *   This program is free software; you can redistribute it and/or modify  *
; *   it under the terms of the GNU General Public License as published by  *
; *   the Free Software Foundation; either version 2 of the License, or     *
; *   (at your option) any later version.                                   *
; *                                                                         *
; *   This program is distributed in the hope that it will be useful,       *
; *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
; *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
; *   GNU General Public License for more details.                          *
; *                                                                         *
; *   You should have received a copy of the GNU General Public License     *
; *   along with this program; if not, write to the                         *
; *   Free Software Foundation, Inc.,                                       *
; *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
; ***************************************************************************/

[DB]
;#### solr ####
solrPhpsUrl = "http://localhost:8080/digizeit/select/?wt=phps"
engine = solr
serialized = STRUCTRUN,ACL
defaultfield = DEFAULT
datefield = DATEINDEXED

;### LUCENE phpJavaBridge ###
;    javabridge = "http://localhost:8087/myJBridge_20111123/java/Java.inc"
;    engine = lucene
;    index = /srv/www/chroot/digizeit/digizeit/lucene
;    stopwordfile = ./stopwords_java.txt
;    defaultfield = 'DEFAULT'
;    datefield = DATEMODIFIED
;    datefield = DATEINDEXED

[MAIN]
error_reporting = 0
;   error_reporting = "E_ALL"
;   if empty or not set, use "sys_get_temp_dir()" 
tmpDir = ""
;   script run time
time_limit = 120
memory_limit = "-1"
formatOutput = 0 
xsl = "./oai2.xsl"
expirationDate = 259200 ; sec = 3 Tage for resumtionToken
resolver = "http://resolver.sub.uni-goettingen.de/purl?"
metsresolver = "http://www.digizeitschriften.de/main/dms/metsresolver/?PPN="
pdfpath = "."
datestamp = "2002-07-01"

[query]
oai_dc = "(PPN:[aaa TO zzz] OR DOCSTRCT:bibliography OR DOCSTRCT:courtdecision OR DOCSTRCT:legalcomment OR DOCSTRCT:legalnorm OR DOCSTRCT:miscella OR DOCSTRCT:review OR DOCSTRCT:poem OR DOCSTRCT:article OR DOCSTRCT:periodicalissue) "
;    oai_dc = "PPN:[aaa TO zzz] "
mets = "(ISWORK:1 OR DOCSTRCT:periodical)"

[HIDECOLLECTIONS]
;    1 = "DC-Identifier"

[OAI-PMH]
xmlns = "http://www.openarchives.org/OAI/2.0/"
xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation = "http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"
[oai-identifier]
xmlns = "http://www.openarchives.org/OAI/2.0/oai-identifier"
xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation = "http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd"
scheme = oai
repositoryIdentifier = "www.digizeitschriften.de"
delimiter = ":"
sampleIdentifier = "oai:www.digizeitschriften.de:PPN123456789"

[metadataFormats]
oai_dc = "dc:title, dc:creator, dc:subject, dc:description, dc:publisher, dc:contributor, dc:date, dc:type, dc:format, dc:identifier, dc:source, dc:language, dc:relation, dc:coverage, dc:rights"
mets = "mets:mets"

[oai_dc]
schema = "http://www.openarchives.org/OAI/2.0/dc.xsd"
metadataNamespace = "http://purl.org/dc/elements/1.1/"
[oai_dc:GetRecord:max]
results = 1
[oai_dc:ListRecords:max]
results = 500
[oai_dc:ListIdentifiers:max]
results = 500
[oai_dc:dc]
xmlns:oai_dc = "http://www.openarchives.org/OAI/2.0/oai_dc/"
xmlns:dc = "http://purl.org/dc/elements/1.1/"
xmlns:xsi = "http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation = "http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd"
[oai_dc:default]
dc:publisher = "DigiZeitschriften e.V."
dc:type = "Text"
[oai_dc:identifier]
PPN = DigiZeit
ISSN = ISSN
ZDB = ZDB

[oai_dc:docstrct]
abstract = "Abstract"
addendum = "Addendum"
advertising = "Advertising"
annotation = "Annotation"
appendix = "Appendix"
article = "Article"
bibliography = "Bibliography"
chapter = "Chapter"
comment = "Comment"
courtdecision = "Court decision"
cover = "Cover"
curriculumvitae = "Curriculum Vitae"
dedication = "dedication"
epilogue = "Epilogue"
errata = "Errata"
figure = "Figure"
imprint = "Imprint"
index = "Index"
indexabbreviations = "List of abbreviations"
indexauthors = "List of authors"
indexchronological = "Chronological list"
indexfigures = "List of figures"
indexlocations = "List of locations"
indexnames = "List of names"
indexspecial = "Special index"
indexsubject = "List of subjects"
indextables = "List of tables"
introduction = "Introduction"
legalcomment = "Legal comment"
legalnorm = "Legal norm"
letter = "Letter"
lettertoeditor = "Letter to editor"
list = "List"
listofpublications = "List of publications"
map = "Map"
miscella = "Miscella"
notes = "nOtes"
obituary = "Obituary"
other = "Other"
periodical = "Periodical"
periodicalissue = "Periodical issue"
periodicalpart = "Periodical part"
periodicalvolume = "Periodical volume"
poem = "Poem"
preface = "Preface"
prepage = "Prepage"
review = "Review"
supplement = "Supplement"
table = "Table"
tableofcontents = "Table of contents"
tableofliteraturerefs = "Table of literature"
theses = "Theses"
titlepage = "Title page"
[oai_dc:rights]
Gesamtabo = "DigiZeitschriften Abo"
free = "Open Access"

[mets]
schema = "http://www.loc.gov/mets/mets.xsd http://www.loc.gov/standards/mods/v3/mods-3-2.xsd"
metadataNamespace = "http://www.loc.gov/METS/ http://www.loc.gov/mods/v3"
[mets:mets]
xmlns:GDZ = "http://gdz.sub.uni-goettingen.de/" 
xmlns:METS = "http://www.loc.gov/METS/" 
xmlns:MODS = "http://www.loc.gov/mods/v3" 
xmlns:ZVDD = "http://namespace.deklarations.dummy/" 
xmlns:dv = "http://dfg-viewer.de/" 
xmlns:xlink = "http://www.w3.org/1999/xlink" 
xsi:schemaLocation = "http://www.loc.gov/METS/ http://www.loc.gov/mets/mets.xsd http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-2.xsd"
[mets:GetRecord:max]
results = 1
[mets:ListRecords:max]
results = 1
[mets:ListIdentifiers:max]
results = 500


[sets]

EU = "Europeana"

JO = "Journals"
JO_OA_ONLY = "Journals - Open Acces only"
JO_SUB_ONLY = "Journals - Subscription only"   
JO_SUB_OA = "Journals - Subscription and Open Access"   

ACL_free = "Open Access"
ACL_gesamtabo = "Grant Access"

DC_020.librarianship = "Librarianship"
DC_020.librarianship_ACL_free = "Librarianship Open Access"
DC_020.librarianship_ACL_gesamtabo = "Librarianship Grant Access"

DC_100.philosophy = "Philosophy"
DC_100.philosophy_ACL_free = "Philosophy Open Access"
DC_100.philosophy_ACL_gesamtabo = "Philosophy Grant Access"

DC_200.religion = "Religion"
DC_200.religion_ACL_free = "Religion Open Access"
DC_200.religion_ACL_gesamtabo = "Religion Grant Access"

DC_300.sociology = "Sociology"
DC_300.sociology_ACL_free = "Sociology Open Access"
DC_300.sociology_ACL_gesamtabo = "Sociology Grant Access"

DC_330.economics = "Economics"
DC_330.economics_ACL_free = "Economics Open Access"
DC_330.economics_ACL_gesamtabo = "Economics Grant Access"

DC_340.law = "Law"
DC_340.law_ACL_free = "Law Open Access"
DC_340.law_ACL_gesamtabo = "Law Grant Access"

DC_370.education = "Education"
DC_370.education_ACL_free = "Education Open Access"
DC_370.education_ACL_gesamtabo = "Education Grant Access"

DC_400.philology = "Philology"
DC_400.philology_ACL_free = "Philology Open Access"
DC_400.philology_ACL_gesamtabo = "Philology Grant Access"

DC_420.english.languages = "English language and literature"
DC_420.english.languages_ACL_free = "English language and literature Open Access"
DC_420.english.languages_ACL_gesamtabo = "English language and literature Grant Access"

DC_430.germanic.languages = "Germanic language and literature"
DC_430.germanic.languages_ACL_free = "Germanic language and literature Open Access"
DC_430.germanic.languages_ACL_gesamtabo = "Germanic language and literature Grant Access"

DC_440.romance.languages = "Romance language and literature"
DC_440.romance.languages_ACL_free = "Romance language and literature Open Access"
DC_440.romance.languages_ACL_gesamtabo = "Romance language and literature Grant Access"

DC_500.sciences = "Sciences"
DC_500.sciences_ACL_free = "Sciences Open Access"
DC_500.sciences_ACL_gesamtabo = "Sciences Grant Access"

DC_510.mathematics_ACL_free = "Mathematics Open Access"

DC_550.geology = "Geology"
DC_550.geology_ACL_free = "Geology Open Access"
DC_550.geology_ACL_gesamtabo = "Geology Grant Access"

DC_700.arts = "Arts"
DC_700.arts_ACL_free = "Arts Open Access"
DC_700.arts_ACL_gesamtabo = "Arts Grant Access"

DC_780.musicology = "Music"
DC_780.musicology_ACL_free = "Music Open Access"
DC_780.musicology_ACL_gesamtabo = "Music Grant Access"

DC_900.history = "History"
DC_900.history_ACL_free = "History Open Access"
DC_900.history_ACL_gesamtabo = "History Grant Access"

DC_953.oriental.studies = "Oriental Studies"
DC_953.oriental.studies_ACL_free = "Oriental Studies Open Access"
DC_953.oriental.studies_ACL_gesamtabo = "Oriental Studies Grant Access"

DC_962.egyptology = "Egyptology and coptology"
DC_962.egyptology_ACL_free = "Egyptology and coptology Open Access"
DC_962.egyptology_ACL_gesamtabo = "Egyptology and coptology Grant Access"

[setqueries]
EU = "(ACL:free AND NOT(ACL:ubheidelberg OR ACL:ubtuebingen OR ACL:ubfrankfurt) AND NOT(DC:510.mathematics AND YEARPUBLISH:[1926 TO 9999]))"
JO = "(DOCSTRCT:periodical)"
JO_OA_ONLY = "(DOCSTRCT:periodical AND (ACL:free AND NOT(ACL:gesamtabo)))"
JO_SUB_ONLY = "(DOCSTRCT:periodical AND (ACL:gesamtabo AND NOT(ACL:free)))"
JO_SUB_OA = "(DOCSTRCT:periodical AND ACL:gesamtabo AND ACL:free)"

[verbs]
GetRecord =
Identify =
ListIdentifiers =
ListMetadataFormats =
ListRecords =
ListSets =

[requestAttributes]
verb =
identifier =
metadataPrefix =
from =
until =
set =
resumptionToken =

[GetRecord]
allowedArguments = "identifier,metadataPrefix"
requiredArguments = "identifier,metadataPrefix"
possibleErrors = "GetRecord=>badArgument,cannotDisseminateFormat,idDoesNotExist"
[Identify]
possibleErrors = "badArgument"
[Identify_tags]
repositoryName = "DigiZeitschriften - OAI Frontend"
baseURL = "http://www.digizeitschriften.de/oai2/"
protocolVersion = "2.0"
adminEmail = "info@sub.uni-goettingen.de"
deletedRecord = "no"
granularity = "YYYY-MM-DD"
[ListIdentifiers]
allowedArguments = "from,until,metadataPrefix,set,resumptionToken"
requiredArguments = "metadataPrefix"
possibleErrors = "badArgument,badResumptionToken,cannotDisseminateFormat,noRecordsMatch,noSetHierarchy" 
[ListMetadataFormats]
allowedArguments = "identifier"
possibleErrors = "badArgument,idDoesNotExist,noMetadataFormats" 
[ListRecords]
allowedArguments = "from,until,metadataPrefix,set,resumptionToken"
requiredArguments = "metadataPrefix"
possibleErrors = "badArgument,badResumptionToken,cannotDisseminateFormat,noRecordsMatch,noSetHierarchy" 
[ListSets]
allowedArguments = "resumptionToken"
possibleErrors = "badArgument,badResumptionToken,noSetHierarchy" 


;cannotDisseminateFormat, idDoesNotExist, badArgument, badVerb, noMetadataFormats, noRecordsMatch, badResumptionToken, noSetHierarchy
[errors]
badArgument = "The argument: _ARG0_ (_VAL0_) included in the request is not valid."
;    badGranularity = "The value: _VAL0_ of the argument: _ARG0_ is not valid."
;    mismatchGranularity = "The format of: _ARG0_=_VAL0_ mismatch to format of: _ARG1_=_VAL1_"
badResumptionToken = "The resumptionToken: _VAL0_ does not exist or has already expired."
;    badRequestMethod = "'The request method: _ARG0_ is unknown."
badVerb = "The verb: _ARG0_ provided in the request is illegal."	
cannotDisseminateFormat = "The metadata format: _VAL0_ given by: _ARG0_ is not supported by this repository."
;    exclusiveArgument = "The usage of resumptionToken as an argument allows no other arguments."
idDoesNotExist = "The value: _VAL0_ of the identifier is illegal for this repository."
;    missingArgument = "The required argument: _ARG0_ is missing in the request."
noRecordsMatch = "The combination of the given values results in an empty list."
noMetadataFormats = "There are no metadata formats available for the specified item (_VAL0_)."
;    noVerb = "The request does not provide any verb."
noSetHierarchy = "This repository does not support sets."
;    sameArgument = "Do not use the same argument more than once."
;    sameVerb = "Do not use verb more than once."
;    default = "Unknown error: _ERR_, argument: _ARG0_, value: _VAL0_"
