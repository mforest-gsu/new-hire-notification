SELECT
  ManagerUserAccount.UserId AS "HRE_MANAGER_USER_ID",
  Manager.EmplId AS "HRE_MANAGER_ID",
  NVL(ManagerBannerUser.SISUSER_FSEM_EMAIL, ManagerUserAccount.ExternalEmail) AS "HRE_MANAGER_EMAIL_ADDRESS",
  ManagerUserAccount.FirstName AS "HRE_MANAGER_FIRST_NAME",
  ManagerUserAccount.LastName AS "HRE_MANAGER_LAST_NAME",
  EmployeeUserAccount.UserId AS "HRE_EMPLOYEE_USER_ID",
  Employee.EmplId AS "HRE_EMPLOYEE_ID",
  NVL(EmployeeBannerUser.SISUSER_FSEM_EMAIL, EmployeeUserAccount.ExternalEmail) AS "HRE_EMPLOYEE_EMAIL_ADDRESS",
  EmployeeUserAccount.FirstName AS "HRE_EMPLOYEE_FIRST_NAME",
  EmployeeUserAccount.LastName AS "HRE_EMPLOYEE_LAST_NAME"
FROM
  MFOREST.HRM_PERSON Manager,
  MFOREST.SISUSER ManagerBannerUser,
  MFOREST.D2L_USER ManagerUserAccount,
  MFOREST.HRM_PERSON Employee,
  MFOREST.SISUSER EmployeeBannerUser,
  MFOREST.D2L_USER EmployeeUserAccount
WHERE
  Manager.Empl_Status = 'A' AND
  ManagerBannerUser.SISUSER_PANTHER_ID = Manager.WhKey AND
  ManagerUserAccount.OrgDefinedId = ManagerBannerUser.SISUSER_ORG_DEFINED_ID AND
  Employee.Reports_To_Id = Manager.EmplId AND
  Employee.Empl_Status = 'A' AND
  EmployeeBannerUser.SISUSER_PANTHER_ID = Employee.WhKey AND
  EmployeeUserAccount.OrgDefinedId = EmployeeBannerUser.SISUSER_ORG_DEFINED_ID AND
  EmployeeUserAccount.UserId IN (
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
  (
    SELECT
      NVL(MIN(HRE_NOTIFY_TIMESTAMP), TO_DATE('31-DEC-9999'))
    FROM
      MFOREST.HRE_NOTIFY o
    WHERE
      o.HRE_NOTIFY_USER_ID = EmployeeUserAccount.UserId AND
      o.HRE_NOTIFY_STATUS_CODE = 'Success'
  ) < (SYSDATE - 30) AND
  (
    SELECT
      NVL(MAX(HRE_NOTIFY_TIMESTAMP), TO_DATE('31-DEC-1999'))
    FROM
      MFOREST.HRE_NOTIFY o
    WHERE
      o.HRE_NOTIFY_USER_ID = ManagerUserAccount.UserId AND
      o.HRE_NOTIFY_STATUS_CODE = 'Success'
  ) < (SYSDATE - 7)
ORDER BY
  ManagerUserAccount.UserId,
  EmployeeUserAccount.UserId
