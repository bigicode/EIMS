import ReportForm from './ReportForm';
import { useReportFormSubmission } from '../../hooks/useReportFormSubmission';

function ReportIssuePage() {
  const { handleReportSubmit } = useReportFormSubmission();
  return <ReportForm onSubmit={handleReportSubmit} />;
}

export default ReportIssuePage;
