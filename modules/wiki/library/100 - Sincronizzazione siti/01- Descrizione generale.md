# Sincronizzazione di GAzie con un sito internet

Questo è un sistema che interfaccia GAzie ad un sito internet semplicemente scambiando i dati da un database ad un altro. Proprio per questo meccanismo che non implica di interagire con il codice del sito, benché sia stato collaudato solo con siti con CMS Joomla, si ritiene debba essere valido anche per altri CMS.
 Al momento la sincronizzazione è stata testata fra GAzie e alcune applicazioni di Joomla, quali ad esempio Hikashop, Virtuemart, Solidres ma, come già detto, per la sua semplicità tecnica è adattabile ad ogni applicazione che utilizzi tabelle di datbase.

 Il tutto si basa su due file di interfaccia, uno presente in GAzie e l'altro presente nella root del sito. Queste doppie interfaccie sono necessarie per poter accedere al database del sito internet dal Gestionale di Gazie che spesso è in locale o su un altro host con dei blocchi di sicurezza.
  L'interfaccia residente su GAzie può rimanere sempre la stessa mentre quella residente sul sito dovrà essere creata specificatamente in base al tipo di componente utilizzato per gestire il negozio online.

 Il meccanismo di funzionamento è semplice; per ovviare al fatto che la maggiorparte dei database non sono gestibili da file residenti all'esterno del dominio cui il database appartiene, una prima interfaccia crea un file xml contenente i dati da gestire. Questo file xml viene trasferito nel dominio del database da modificare e da lì, con una seconda interfaccia verrà letto e utilizzato per modificare il database. Una sorta di ... cavallo di Troia.
