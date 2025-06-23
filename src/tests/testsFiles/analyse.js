const fs = require('fs');

fs.readFile('./resultats.json', 'utf8', (err, data) => {
    if (err) {
        console.error('Erreur lecture fichier:', err);
        return;
    }

    let json;
    try {
        json = JSON.parse(data);
    } catch (e) {
        console.error('Erreur parsing JSON:', e);
        return;
    }

    const counters = json.aggregate?.counters || {};
    const rates = json.aggregate?.rates || {};
    const summaries = json.aggregate?.summaries || {};

    const totalRequests = counters['http.requests'] || 'N/A';
    const http200 = counters['http.codes.200'] || 'N/A';
    const vusersCreated = counters['vusers.created'] || 'N/A';

    const meanResponseTime = summaries['http.response_time']?.mean || 'N/A';
    const p95ResponseTime = summaries['http.response_time']?.p95 || 'N/A';

    console.log('--- Résumé du test ---');
    console.log(`Requêtes totales : ${totalRequests}`);
    console.log(`Réponses HTTP 200 : ${http200}`);
    console.log(`Temps moyen de réponse (ms) : ${meanResponseTime}`);
    console.log(`P95 (ms) : ${p95ResponseTime}`);
    console.log(`Utilisateurs virtuels : ${vusersCreated}`);
});