SELECT
  UserAccount.UserId AS "HRE_EMPLOYEE_USER_ID",
  0 as "HRE_EMPLOYEE_ID",
  NVL(BannerUser.SISUSER_FSEM_EMAIL, UserAccount.ExternalEmail) AS "HRE_EMPLOYEE_EMAIL_ADDRESS",
  UserAccount.FirstName AS "HRE_EMPLOYEE_FIRST_NAME",
  UserAccount.LastName AS "HRE_EMPLOYEE_LAST_NAME"
FROM
  MFOREST.SISUSER BannerUser,
  MFOREST.D2L_USER UserAccount
WHERE
  UserAccount.OrgDefinedId = BannerUser.SISUSER_ORG_DEFINED_ID AND
  UserAccount.UserId IN (
    SELECT
      UserEnrollment.UserId
    FROM
      MFOREST.D2L_USER_ENROLLMENT UserEnrollment,
      MFOREST.D2L_QUIZ_OBJECT QuizObject
    WHERE
      UserEnrollment.OrgUnitId = :OrgUnitId AND
      UserEnrollment.RoleName = 'Student' AND
      QuizObject.OrgUnitId = :OrgUnitId AND
      QuizObject.IsActive = 1 AND
      NOT EXISTS (
        SELECT
          1
        FROM
          MFOREST.D2L_QUIZ_ATTEMPT QuizAttempt
        WHERE
          QuizAttempt.QuizId = QuizObject.QuizId AND
          QuizAttempt.OrgUnitId = :OrgUnitId AND
          QuizAttempt.UserId = UserEnrollment.UserId AND
          QuizAttempt.IsGraded = 1 AND
          QuizAttempt.IsDeleted = 0 AND
          QuizAttempt.Score = QuizAttempt.PossibleScore
      )
    GROUP BY
      UserEnrollment.UserId
  ) AND
  NOT EXISTS (
    SELECT
      1
    FROM
      MFOREST.HRE_NOTIFY o
    WHERE
      o.HRE_NOTIFY_USER_ID = UserAccount.UserId AND
      o.HRE_NOTIFY_STATUS_CODE = 'Success'
  )
