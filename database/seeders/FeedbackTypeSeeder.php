<?php

namespace Database\Seeders;

use App\Models\FeedbackType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeedbackTypeSeeder extends Seeder
{
    private function feedbackTypes()
    {
        return [
            ['name' => 'keine Methodik vorgegeben',
                'description' => 'Keine bestimmte Methodik vorgegeben. Gestalte das Feedback frei. Halte dich kurz und konzentriere dich auf das Wesentliche. '
            ],
            ['name' => 'WWW',
                'description' => 'WWW steht für Wahrnehmung, Wirkung, Wunsch. Das Feedback soll in diesen 3 Kategorien gegeben werden. Wahrnehmung = Konkrete Schilderung von Beobachtungen. Wirkung = Schilderung, wie die Situation oder das Verhalten auf dich gewirkt hat. Das funktioniert bei positiven wie negativen Feedback. Wunsch = Was könnte man in Zukunft besser machen. Halte dich kurz und knapp. Gib zum Schluss noch ein ganz kurzes Fazit. Strukturiere dein Feedback in 3 Schritten: Mir ist aufgefallen, dass du…, Mein Eindruck war, dass du…, Ich würde mich freuen, wenn du…'
            ],
            ['name' => 'Sandwich',
                'description' => 'Kritik wird zwischen 2 positiven Aspekten verpackt. Der erste positive Punkt ist eine konkrete positive Beobachtung. Dann kommt ggf. die Kritik. Wenn es keine gibt, sage das auch. Der dritte Punkt ist nochmal ein allgemeiner positiver Aspekt. Halte dich kurz und konzentriere dich auf das Wesentliche.'
            ],
            ['name' => 'STATE',
                'description' => 'Die Bezeichnung STATE  setzt sich aus den englischen Bezeichnungen der Methode zusammen: Share the facts: Wahrnehmung äußern, Tell your story: Wirkung (der Wahrnehmung) mitteilen, Ask for others’ paths: Gegenüber nach eigener Sicht fragen, Talk tentatively: Formuliere zurückhaltend, Encourage testing: Gegenüber ermutigen gegensätzliche Meinung zu äußern. Strukturiere dein Feedback in diesen Schritten: Mir ist aufgefallen, dass du.., Ich habe mich gefragt, ob du das deshalb machst, weil…, Kannst du mir sagen, warum du … so machst?, Kannst du mir sagen, was ich vielleicht übersehen habe?'
            ],
            ['name' => 'Client',
                'description' => 'Wertung 1 für sehr, 2 für meistens, 3 für nur teilweise 4 für wenig: Mit der Beratung bin ich grundsätzlich zufrieden. Der Berater hat mein Anliegen verstanden. Der Berater nahm meine Frage ernst. Die Beratung war hilfreich zur Klärung meines Anliegens. Die Antwort hat mir eine neue Perspektive ermöglicht. Erkenntnisse konnte ich in meine Praxis umsetzen. Der Berater hat die richtigen Worte gewählt, den richtigen Tonfall getroffen. Bei diesem Berater würde ich mich wieder melden. Freies Textfeedback: Weitere Rückmeldungen. Du sollst in folgendem JSON Format antworten und deine Werte einsetzen. {"zufrieden": "", "verstanden": "", "ernst_genommen": "", "hilfreich": "", "neue_perspektive": "", "umsetzung_in_praxis": "", "richtige_worte": "", "wieder_melden": "", "freies_textfeedback": "", "erklaerung_bewertung": "" } Gib bitte auch konkretes Feedback in "freies_textfeedback" womit du besonders zufrieden warst, oder womit auch nicht. Der Berater soll verstehen was besser gemacht werden könnte. In "erklaerung_bewertung" sollst du kurz und knapp erklären aus welchen Gründen du dich für 1, 2, 3 oder 4 entschieden hast. Die Form soll so aussehen: zufrieden (wertung), weil..., verstanden (wertung), weil.., ... . Wertung 3 oder 4 sollte nur verwendet werden wenn du mit etwas nicht zufrieden warst. '
            ]
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->feedbackTypes() as $type) {

            FeedbackType::create([
                'name' => $type['name'],
                'description' => $type['description'],

            ]);
        }
    }
}
