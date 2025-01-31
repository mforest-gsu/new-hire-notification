SELECT
  QuizObject.QuizId AS "Id",
  QuizObject.QuizName AS "Name",
  NVL((
    SELECT DISTINCT
        1
    FROM
        MFOREST.D2L_QUIZ_ATTEMPT QuizAttempt
    WHERE
        QuizAttempt.OrgUnitId = :OrgUnitId AND
        QuizAttempt.UserId = :UserId AND
        QuizAttempt.QuizId = QuizObject.QuizId AND
        QuizAttempt.IsGraded = 1 AND
        QuizAttempt.IsDeleted = 0 AND
        QuizAttempt.Score = QuizAttempt.PossibleScore
  ), 0) AS "Completed"
FROM
  MFOREST.D2L_QUIZ_OBJECT QuizObject
WHERE
  QuizObject.OrgUnitId = :OrgUnitId AND
  QuizObject.IsActive = 1
