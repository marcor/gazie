# FATTURE ELETTRONICHE

Le fatture elettroniche sono gestite da GAzie tramite la casella elettronica PEC.

Per ricevere ed inviare fatture elettroniche deve essere configurata una mail PEC per ogni azienda che si sta gestendo.

I dati di configurazione si trovano nella __configurazione avanzata__ dell'azienda.
Occorre inserire:

+ *Casella di posta* (si intende la casella PEC)
+ *Password* (la password utilizzata per la PEC)
+ *Configurazione pop imap* (per ARUBA ad esempio va messo l'imap **{imaps.pec.aruba.it:993/imap/ssl}INBOX**

Con questi 3 dati si possono il sistema può verificare attraverso IMAP le fatture elettroniche di acquisto che avrete sulla pec (con la configurazione IMAP la posta non verrà eliminata).

Se utilizzate la PEC per altri scopi, potete inserire un filtro:ù

+ *Filtro casella di posta* (ad esempio inserite "**FROM sdi21@pec.fatturapa.it**" per filtrare tutte quelle email che arriva dallo sdi21.


