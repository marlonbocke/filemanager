# Filemanager

Ein dateibasierter Web-Dateimanager in PHP. Das Projekt befindet sich noch in
Entwicklung und dient aktuell als Lern- und Portfolio-Projekt.

## Funktionen

- Anmeldung über eine MySQL-Datenbank
- Dateien und Ordner anzeigen und verwalten
- Dateien hochladen und herunterladen
- Ordner erstellen
- Dateien und Ordner umbenennen, verschieben und löschen
- Vorschau für unterstützte Dateien
- Separater, experimenteller Bereich für die Zwei-Faktor-Authentifizierung

Der Funktionsumfang und die einzelnen Bereiche sind noch nicht vollständig
vereinheitlicht oder getestet.

## Voraussetzungen

- PHP 8.3 oder neuer
- Webserver mit PHP-Unterstützung
- MySQL oder MariaDB
- PHP-Erweiterung `mysqli`
- Für den experimentellen 2FA-Bereich zusätzlich Composer

PHP 8.3 wird unter anderem wegen `json_validate()` in `index.php` benötigt.

## Entwicklungsstand und Einrichtung

Eine vollständige Installation ist derzeit noch nicht dokumentiert. Die
Anwendung erwartet eine konfigurierte MySQL-Datenbank mit einer `users`-Tabelle
und den im Code verwendeten Feldern. Ein Datenbankschema ist in diesem
Repository noch nicht enthalten.

Die Konfigurationsseite kann eine lokale Datenbankkonfiguration speichern.
Diese Konfiguration enthält Zugangsdaten und gehört nicht ins Repository.
Verwende ausschließlich eigene Entwicklungs-Zugangsdaten und stelle die
Anwendung nicht öffentlich erreichbar bereit, bevor die unten genannten
Sicherheitspunkte behoben sind.

## Sicherheitshinweis

Dieses Projekt ist ein unfertiger Prototyp und **nicht für den produktiven
Einsatz oder die Bereitstellung im öffentlichen Internet geeignet**. Die
Authentifizierung, Dateizugriffe und Konfiguration müssen vor einem Einsatz
weiter geprüft und abgesichert werden. Insbesondere müssen hinterlegte
Beispiel-Zugangsdaten entfernt und offengelegte Zugangsdaten geändert werden.

## Tests

Automatisierte Tests und ein vollständiger Installations-/Testablauf sind
derzeit nicht enthalten.
