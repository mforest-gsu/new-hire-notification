SELECT
  MAX(OrgUnitId) AS "OrgUnitId"
FROM
  MFOREST.D2L_ORGANIZATIONAL_UNIT OrgUnit
WHERE
  OrgUnit.Organization = 'Georgia State' AND
  OrgUnit.Type = 'Course Offering' AND
  OrgUnit.Name = 'New Hire Required Training' AND
  OrgUnit.IsActive = 1 AND
  OrgUnit.IsDeleted = 0
