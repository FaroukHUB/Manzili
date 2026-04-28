import {
  pgTable,
  uuid,
  text,
  integer,
  boolean,
  timestamp,
  date,
  numeric,
} from "drizzle-orm/pg-core"

export const USER_ID = "user_1"

export const settings = pgTable("settings", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  hourlyRate: numeric("hourly_rate", { precision: 10, scale: 2 }).notNull().default("10"),
  dailyGoal: numeric("daily_goal", { precision: 10, scale: 2 }).notNull().default("80"),
  weeklyGoal: numeric("weekly_goal", { precision: 10, scale: 2 }).notNull().default("480"),
  monthlyGoal: numeric("monthly_goal", { precision: 10, scale: 2 }).notNull().default("1920"),
  familyExpense: numeric("family_expense", { precision: 10, scale: 2 }).notNull().default("250"),
  personalExpense: numeric("personal_expense", { precision: 10, scale: 2 }).notNull().default("80"),
  currency: text("currency").notNull().default("€"),
  defaultStartTime: text("default_start_time").notNull().default("10:00"),
  defaultEndTime: text("default_end_time").notNull().default("18:00"),
  defaultBreakMinutes: integer("default_break_minutes").notNull().default(0),
  updatedAt: timestamp("updated_at").defaultNow(),
})

export const workLogs = pgTable("work_logs", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  date: date("date").notNull(),
  startTime: text("start_time").notNull(),
  endTime: text("end_time").notNull(),
  breakMinutes: integer("break_minutes").notNull().default(0),
  hourlyRate: numeric("hourly_rate", { precision: 10, scale: 2 }).notNull(),
  hoursWorked: numeric("hours_worked", { precision: 10, scale: 2 }).notNull(),
  earnings: numeric("earnings", { precision: 10, scale: 2 }).notNull(),
  source: text("source").notNull().default("pizzeria"),
  note: text("note"),
  createdAt: timestamp("created_at").defaultNow(),
})

export const debts = pgTable("debts", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  name: text("name").notNull(),
  totalAmount: numeric("total_amount", { precision: 10, scale: 2 }).notNull(),
  paidAmount: numeric("paid_amount", { precision: 10, scale: 2 }).notNull().default("0"),
  priority: integer("priority").notNull().default(1),
  dueDate: date("due_date"),
  note: text("note"),
  whatsappNumber: text("whatsapp_number"),
  isActive: boolean("is_active").notNull().default(true),
  createdAt: timestamp("created_at").defaultNow(),
})

export const debtPayments = pgTable("debt_payments", {
  id: uuid("id").primaryKey().defaultRandom(),
  debtId: uuid("debt_id")
    .references(() => debts.id)
    .notNull(),
  amount: numeric("amount", { precision: 10, scale: 2 }).notNull(),
  paymentDate: date("payment_date").notNull(),
  note: text("note"),
  createdAt: timestamp("created_at").defaultNow(),
})

export const receivables = pgTable("receivables", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  name: text("name").notNull(),
  reason: text("reason"),
  totalAmount: numeric("total_amount", { precision: 10, scale: 2 }).notNull(),
  receivedAmount: numeric("received_amount", { precision: 10, scale: 2 }).notNull().default("0"),
  expectedDate: date("expected_date"),
  status: text("status").notNull().default("pending"),
  note: text("note"),
  whatsappNumber: text("whatsapp_number"),
  createdAt: timestamp("created_at").defaultNow(),
})

export const fixedExpenses = pgTable("fixed_expenses", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  name: text("name").notNull(),
  amount: numeric("amount", { precision: 10, scale: 2 }).notNull(),
  category: text("category").notNull().default("other"),
  frequency: text("frequency").notNull().default("monthly"), // "monthly" | "one_time"
  expenseDate: date("expense_date"), // for one_time expenses
  isActive: boolean("is_active").notNull().default(true),
  createdAt: timestamp("created_at").defaultNow(),
})

export const assets = pgTable("assets", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  name: text("name").notNull(),
  amount: numeric("amount", { precision: 10, scale: 2 }).notNull().default("0"),
  note: text("note"),
  updatedAt: timestamp("updated_at").defaultNow(),
})

export const contracts = pgTable("contracts", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  clientName: text("client_name").notNull(),
  description: text("description"),
  totalAmount: numeric("total_amount", { precision: 10, scale: 2 }).notNull(),
  depositReceived: numeric("deposit_received", { precision: 10, scale: 2 }).notNull().default("0"),
  status: text("status").notNull().default("signed"),
  expectedDate: date("expected_date"),
  deliveredAt: date("delivered_at"),
  note: text("note"),
  whatsappNumber: text("whatsapp_number"),
  createdAt: timestamp("created_at").defaultNow(),
})

export const businessActivities = pgTable("business_activities", {
  id: uuid("id").primaryKey().defaultRandom(),
  userId: text("user_id").notNull().default("user_1"),
  name: text("name").notNull(),
  type: text("type").notNull().default("service"),
  unit: text("unit"),
  pricePerUnit: numeric("price_per_unit", { precision: 10, scale: 2 }),
  isActive: boolean("is_active").notNull().default(true),
  createdAt: timestamp("created_at").defaultNow(),
})

export const businessTransactions = pgTable("business_transactions", {
  id: uuid("id").primaryKey().defaultRandom(),
  activityId: uuid("activity_id")
    .references(() => businessActivities.id)
    .notNull(),
  userId: text("user_id").notNull().default("user_1"),
  clientName: text("client_name"),
  status: text("status").notNull().default("prospect"),
  quantity: numeric("quantity", { precision: 10, scale: 2 }),
  totalPrice: numeric("total_price", { precision: 10, scale: 2 }),
  depositReceived: numeric("deposit_received", { precision: 10, scale: 2 }).default("0"),
  balanceDue: numeric("balance_due", { precision: 10, scale: 2 }),
  contactedAt: date("contacted_at"),
  signedAt: date("signed_at"),
  deliveryDate: date("delivery_date"),
  note: text("note"),
  createdAt: timestamp("created_at").defaultNow(),
})

export type Settings = typeof settings.$inferSelect
export type WorkLog = typeof workLogs.$inferSelect
export type Debt = typeof debts.$inferSelect
export type DebtPayment = typeof debtPayments.$inferSelect
export type Receivable = typeof receivables.$inferSelect
export type FixedExpense = typeof fixedExpenses.$inferSelect
export type BusinessActivity = typeof businessActivities.$inferSelect
export type BusinessTransaction = typeof businessTransactions.$inferSelect
export type Asset = typeof assets.$inferSelect
export type Contract = typeof contracts.$inferSelect
