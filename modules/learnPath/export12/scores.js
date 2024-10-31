/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/*
 *
 * Check the answers of a scorm quiz.
 *
 */

function CalculateRawScore(objDoc, idCount, fillin)
{
    var i;
    var eltId;
    var element;

    var score = 0;

    var questionType;
    var questionNum;
    var answerNum;

    var myRegexp = /^(.*)_(.*)_(.*)/
    var myMatch;


    // Loop over every element with an interresting id ("scorm_*")
    for (i = 0; i < idCount; i++)
    {
        eltId = 'scorm_' + i;
        element = objDoc.getElementById(eltId);

        myMatch = myRegexp.exec(element.name);
        questionType = myMatch[1];
        questionNum = myMatch[2];
        answerNum = myMatch[3];

        switch (questionType)
        {
            case 'unique':
            case 'multiple':
                if (element.checked)
                {
                    score += (+element.value);
                }
                break;

            case 'matching':
                score += (+element.value);
                break;

            case 'fill':
                var textIn = element.value;
                if (textIn.toUpperCase() == fillin[element.name][0].toUpperCase())
                {
                    var w = fillin[element.name][1];
                    score += (+w);
                }
                break;

        }
    }

    return score;


}
