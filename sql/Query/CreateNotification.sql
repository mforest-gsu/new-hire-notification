INSERT INTO MFOREST.HRE_NOTIFY
  (
    HRE_NOTIFY_ID,
    HRE_NOTIFY_USER_ID,
    HRE_NOTIFY_TEMPLATE,
    HRE_NOTIFY_TIMESTAMP,
    HRE_NOTIFY_CONTEXT,
    HRE_NOTIFY_STATUS_CODE,
    HRE_NOTIFY_STATUS_DESC
  )
VALUES
  (
    :HRE_NOTIFY_ID,
    :HRE_NOTIFY_USER_ID,
    :HRE_NOTIFY_TEMPLATE,
    TO_DATE(:HRE_NOTIFY_TIMESTAMP, 'yyyy-mm-dd hh24:mi:ss'),
    :HRE_NOTIFY_CONTEXT,
    :HRE_NOTIFY_STATUS_CODE,
    :HRE_NOTIFY_STATUS_DESC
  )
