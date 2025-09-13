// User types
export interface User {
  id: number
  tenant_id: number
  first_name: string
  last_name: string
  email: string
  phone?: string
  nmls_id?: string
  license_number?: string
  profile_data?: Record<string, any>
  compliance_data?: Record<string, any>
  is_active: boolean
  last_login_at?: string
  created_at: string
  updated_at: string
  roles?: Role[]
  permissions?: Permission[]
}

export interface Role {
  id: number
  name: string
  display_name: string
  description?: string
  permissions?: Permission[]
}

export interface Permission {
  id: number
  name: string
  display_name: string
  description?: string
}

// Tenant types
export interface Tenant {
  id: number
  name: string
  domain: string
  database: string
  configuration?: Record<string, any>
  compliance_settings?: Record<string, any>
  is_active: boolean
  trial_ends_at?: string
  subscription_ends_at?: string
  created_at: string
  updated_at: string
}

// Loan types
export interface Loan {
  id: number
  tenant_id: number
  loan_number: string
  status: LoanStatus
  loan_officer_id: number
  borrower_id?: number
  co_borrower_id?: number
  loan_data?: Record<string, any>
  compliance_data?: Record<string, any>
  workflow_data?: Record<string, any>
  loan_amount: number
  interest_rate?: number
  loan_type: LoanType
  property_type: PropertyType
  occupancy_type: OccupancyType
  purpose: LoanPurpose
  application_date: string
  closing_date?: string
  funding_date?: string
  vendor_integrations?: Record<string, any>
  audit_trail?: Record<string, any>[]
  created_at: string
  updated_at: string
  borrower?: Borrower
  co_borrower?: Borrower
  loan_officer?: User
  workflow_steps?: WorkflowStep[]
}

export type LoanStatus = 
  | 'application'
  | 'processing'
  | 'underwriting'
  | 'approved'
  | 'denied'
  | 'closed'
  | 'funded'

export type LoanType = 
  | 'conventional'
  | 'fha'
  | 'va'
  | 'usda'
  | 'jumbo'

export type PropertyType = 
  | 'single_family'
  | 'condo'
  | 'townhouse'
  | 'multi_family'

export type OccupancyType = 
  | 'primary'
  | 'secondary'
  | 'investment'

export type LoanPurpose = 
  | 'purchase'
  | 'refinance'
  | 'cash_out'

// Borrower types
export interface Borrower {
  id: number
  tenant_id: number
  first_name: string
  last_name: string
  email?: string
  phone?: string
  ssn?: string
  date_of_birth?: string
  marital_status?: string
  address_data?: AddressData
  employment_data?: EmploymentData
  income_data?: IncomeData
  asset_data?: AssetData
  liability_data?: LiabilityData
  compliance_data?: Record<string, any>
  is_primary: boolean
  created_at: string
  updated_at: string
}

export interface AddressData {
  street_address: string
  city: string
  state: string
  zip_code: string
  country: string
  years_at_address?: number
  months_at_address?: number
  mailing_address?: {
    street_address: string
    city: string
    state: string
    zip_code: string
    country: string
  }
}

export interface EmploymentData {
  employer_name: string
  job_title: string
  employment_type: 'full_time' | 'part_time' | 'self_employed' | 'contract' | 'unemployed'
  years_employed?: number
  months_employed?: number
  annual_income?: number
  monthly_income?: number
  employer_address?: AddressData
  previous_employment?: EmploymentData[]
}

export interface IncomeData {
  base_income: number
  overtime_income?: number
  bonus_income?: number
  commission_income?: number
  rental_income?: number
  other_income?: number
  total_monthly_income: number
  income_sources: IncomeSource[]
}

export interface IncomeSource {
  type: string
  amount: number
  frequency: 'weekly' | 'bi_weekly' | 'monthly' | 'annually'
  description?: string
}

export interface AssetData {
  checking_accounts: BankAccount[]
  savings_accounts: BankAccount[]
  investment_accounts: InvestmentAccount[]
  real_estate: RealEstateAsset[]
  vehicles: VehicleAsset[]
  other_assets: OtherAsset[]
  total_assets: number
}

export interface BankAccount {
  institution_name: string
  account_type: 'checking' | 'savings'
  account_number?: string
  current_balance: number
  account_holder: string
}

export interface InvestmentAccount {
  institution_name: string
  account_type: string
  current_value: number
  account_holder: string
}

export interface RealEstateAsset {
  property_address: string
  property_type: string
  current_value: number
  mortgage_balance?: number
  monthly_rent?: number
  account_holder: string
}

export interface VehicleAsset {
  year: number
  make: string
  model: string
  current_value: number
  loan_balance?: number
  account_holder: string
}

export interface OtherAsset {
  description: string
  current_value: number
  account_holder: string
}

export interface LiabilityData {
  credit_cards: CreditCard[]
  loans: LoanLiability[]
  mortgages: MortgageLiability[]
  other_liabilities: OtherLiability[]
  total_monthly_payments: number
}

export interface CreditCard {
  creditor_name: string
  account_number?: string
  current_balance: number
  monthly_payment: number
  account_holder: string
}

export interface LoanLiability {
  creditor_name: string
  loan_type: string
  current_balance: number
  monthly_payment: number
  account_holder: string
}

export interface MortgageLiability {
  property_address: string
  creditor_name: string
  current_balance: number
  monthly_payment: number
  account_holder: string
}

export interface OtherLiability {
  description: string
  current_balance: number
  monthly_payment: number
  account_holder: string
}

// Workflow types
export interface WorkflowStep {
  id: number
  tenant_id: number
  loan_id: number
  step_name: string
  step_type: string
  step_order: number
  is_completed: boolean
  is_required: boolean
  completion_criteria?: Record<string, any>
  assigned_to?: number
  due_date?: string
  completed_at?: string
  completed_by?: number
  step_data?: Record<string, any>
  compliance_requirements?: Record<string, any>
  created_at: string
  updated_at: string
  assigned_user?: User
  completed_by_user?: User
}

// Compliance types
export interface ComplianceAudit {
  id: number
  tenant_id: number
  audit_type: string
  entity_type: string
  entity_id: number
  action: string
  old_values?: Record<string, any>
  new_values?: Record<string, any>
  metadata?: Record<string, any>
  user_id?: number
  ip_address?: string
  user_agent?: string
  created_at: string
  user?: User
}

export interface ComplianceViolation {
  regulation: string
  type: string
  message: string
  severity: 'low' | 'medium' | 'high' | 'critical'
  field?: string
  disclosure?: string
  notice?: string
}

export interface ComplianceSummary {
  is_compliant: boolean
  total_violations: number
  critical_violations: number
  high_violations: number
  violations_by_regulation: Record<string, number>
}

// Document types
export interface Document {
  id: number
  tenant_id: number
  loan_id?: number
  borrower_id?: number
  name: string
  type: string
  category: string
  file_path: string
  file_size: number
  mime_type: string
  is_verified: boolean
  verified_at?: string
  verified_by?: number
  metadata?: Record<string, any>
  created_at: string
  updated_at: string
  verified_by_user?: User
}

// Integration types
export interface Integration {
  id: number
  tenant_id: number
  name: string
  type: string
  status: 'active' | 'inactive' | 'error'
  configuration: Record<string, any>
  last_sync_at?: string
  error_message?: string
  created_at: string
  updated_at: string
}

// API types
export interface ApiResponse<T = any> {
  data: T
  message?: string
  meta?: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
  code?: string
}

// Form types
export interface LoginCredentials {
  email: string
  password: string
  remember?: boolean
}

export interface RegisterData {
  first_name: string
  last_name: string
  email: string
  password: string
  password_confirmation: string
  phone?: string
  nmls_id?: string
  license_number?: string
}

export interface LoanFilters {
  status: string
  loan_officer_id: string
  date_from: string
  date_to: string
  search: string
}

// Dashboard types
export interface DashboardStats {
  total_loans: number
  active_loans: number
  completed_loans: number
  total_loan_amount: number
  average_loan_amount: number
  compliance_violations: number
  pending_approvals: number
  overdue_tasks: number
}

export interface LoanSummary {
  status: LoanStatus
  count: number
  percentage: number
  total_amount: number
}

export interface ComplianceSummary {
  total_violations: number
  critical_violations: number
  high_violations: number
  violations_by_regulation: Record<string, number>
}

export interface WorkflowSummary {
  total_steps: number
  completed_steps: number
  overdue_steps: number
  pending_steps: number
}

// Notification types
export interface Notification {
  id: number
  type: 'info' | 'success' | 'warning' | 'error'
  title: string
  message: string
  is_read: boolean
  created_at: string
  action_url?: string
  action_text?: string
}

// Theme types
export interface ThemeConfiguration {
  primary_color: string
  secondary_color: string
  logo_url?: string
  favicon_url?: string
  custom_css?: string
}

export interface BrandingConfiguration {
  company_name: string
  tagline: string
  support_email: string
  support_phone: string
  website_url?: string
  social_media?: Record<string, string>
}