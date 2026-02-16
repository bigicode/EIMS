import { useCallback } from 'react';
import { useNotifications } from './useNotifications';

export const useReportFormSubmission = () => {
  const { addNotification, addToast } = useNotifications();

  const handleReportSubmit = useCallback(
    async (formData) => {
      await addNotification({
        title: formData.name,
        description: formData.description,
        imei: formData.imei,
        deviceNumber: formData.deviceNumber,
        type: formData.type,
        action: formData.action,
        priority: formData.priority,
        dateReported: new Date().toISOString().split('T')[0],
        status: 'unresolved',
      });

      addToast('Issue reported successfully.', 'success');
    },
    [addNotification, addToast]
  );

  return { handleReportSubmit };
};
